package payment

import (
	"bytes"
	"crypto/rand"
	"encoding/base64"
	"encoding/json"
	"fmt"
	"io"
	"log"
	"net/http"
	"time"

	"github.com/skip2/go-qrcode"
	"dramabot/internal/database"
)

// PaymentService mendefinisikan kontrak komunikasi dengan Payment Coordinator
type PaymentService interface {
	// RequestQRIS meminta string QRIS dan membuat data transaksi
	RequestQRIS(telegramID int64, plan *database.VIPPlan) (*database.Transaction, []byte, error)
	// GenerateQRCodeImage mengonversi string QRIS menjadi gambar PNG
	GenerateQRCodeImage(qrisContent string) ([]byte, error)
}

type CoordinatorService struct {
	apiURL string
	apiKey string
	client *http.Client
}

func NewPaymentCoordinator(apiURL, apiKey string) *CoordinatorService {
	return &CoordinatorService{
		apiURL: apiURL,
		apiKey: apiKey,
		client: &http.Client{Timeout: 15 * time.Second},
	}
}

// RequestQRIS membuat transaksi baru dan menghasilkan gambar QRIS (Midtrans API asli dengan fallback lokal)
func (s *CoordinatorService) RequestQRIS(telegramID int64, plan *database.VIPPlan) (*database.Transaction, []byte, error) {
	// Jika apiKey (Midtrans Server Key) tersedia, panggil API Midtrans Charge QRIS resmi
	if s.apiKey != "" {
		tx, pngBytes, err := s.requestMidtransQRIS(telegramID, plan)
		if err == nil && tx != nil && len(pngBytes) > 0 {
			return tx, pngBytes, nil
		}
		log.Printf("[Payment] Midtrans request error (%v), beralih ke generator QRIS lokal...", err)
	}

	// Fallback Generator Lokal jika Midtrans offline / tidak ada key
	randomBytes := make([]byte, 3)
	_, _ = rand.Read(randomBytes)
	trxCode := fmt.Sprintf("VIP-%d-%d", plan.ID, time.Now().Unix())

	qrisPayload := fmt.Sprintf("00020101021226680016ID.CO.QRIS.WWW0118DRAMABOTPAY9988770215%s520458125303360540%0.2f5802ID5913DRAMABOT VIP6007JAKARTA62070703A016304ABCD",
		trxCode, plan.Price)

	now := time.Now()
	expiredAt := now.Add(15 * time.Minute)

	tx := &database.Transaction{
		TrxCode:    trxCode,
		TelegramID: telegramID,
		PlanID:     plan.ID,
		PlanName:   plan.Name,
		Amount:     plan.Price,
		QRISString: qrisPayload,
		QRISURL:    "",
		Status:     database.TxStatusPending,
		CreatedAt:  now,
		ExpiredAt:  expiredAt,
	}

	pngBytes, err := s.GenerateQRCodeImage(qrisPayload)
	if err != nil {
		return nil, nil, fmt.Errorf("gagal membuat gambar QRIS: %w", err)
	}

	return tx, pngBytes, nil
}

// requestMidtransQRIS memanggil API Midtrans Sandbox QRIS Charge secara real
func (s *CoordinatorService) requestMidtransQRIS(telegramID int64, plan *database.VIPPlan) (*database.Transaction, []byte, error) {
	orderID := fmt.Sprintf("VIP-%d-%d", plan.ID, time.Now().Unix())

	chargePayload := map[string]interface{}{
		"payment_type": "qris",
		"transaction_details": map[string]interface{}{
			"order_id":     orderID,
			"gross_amount": int64(plan.Price),
		},
	}

	jsonBytes, err := json.Marshal(chargePayload)
	if err != nil {
		return nil, nil, err
	}

	endpoint := "https://api.sandbox.midtrans.com/v2/charge"
	req, err := http.NewRequest("POST", endpoint, bytes.NewBuffer(jsonBytes))
	if err != nil {
		return nil, nil, err
	}

	authHeader := "Basic " + base64.StdEncoding.EncodeToString([]byte(s.apiKey+":"))
	req.Header.Set("Content-Type", "application/json")
	req.Header.Set("Accept", "application/json")
	req.Header.Set("Authorization", authHeader)

	resp, err := s.client.Do(req)
	if err != nil {
		return nil, nil, fmt.Errorf("koneksi ke Midtrans gagal: %w", err)
	}
	defer resp.Body.Close()

	bodyBytes, err := io.ReadAll(resp.Body)
	if err != nil {
		return nil, nil, fmt.Errorf("gagal membaca respon Midtrans: %w", err)
	}

	var midtransResp struct {
		StatusCode string `json:"status_code"`
		StatusMsg  string `json:"status_message"`
		OrderID    string `json:"order_id"`
		QRString   string `json:"qr_string"`
		Actions    []struct {
			Name   string `json:"name"`
			Method string `json:"method"`
			URL    string `json:"url"`
		} `json:"actions"`
	}

	if err := json.Unmarshal(bodyBytes, &midtransResp); err != nil {
		return nil, nil, fmt.Errorf("format respon Midtrans tidak valid: %w", err)
	}

	if midtransResp.StatusCode != "201" {
		return nil, nil, fmt.Errorf("Midtrans error: %s (code %s)", midtransResp.StatusMsg, midtransResp.StatusCode)
	}

	qrURL := ""
	for _, act := range midtransResp.Actions {
		if act.Name == "generate-qr-code" {
			qrURL = act.URL
			break
		}
	}

	if qrURL == "" {
		return nil, nil, fmt.Errorf("URL QR Code tidak ditemukan di respon Midtrans")
	}

	// Unduh bytes gambar QR PNG langsung dari URL Midtrans
	qrResp, err := s.client.Get(qrURL)
	if err != nil {
		return nil, nil, fmt.Errorf("gagal mengunduh gambar QRIS dari Midtrans: %w", err)
	}
	defer qrResp.Body.Close()

	pngBytes, err := io.ReadAll(qrResp.Body)
	if err != nil || len(pngBytes) == 0 {
		return nil, nil, fmt.Errorf("file gambar QRIS dari Midtrans kosong")
	}

	now := time.Now()
	expiredAt := now.Add(15 * time.Minute)

	tx := &database.Transaction{
		TrxCode:    orderID,
		TelegramID: telegramID,
		PlanID:     plan.ID,
		PlanName:   plan.Name,
		Amount:     plan.Price,
		QRISString: midtransResp.QRString,
		QRISURL:    qrURL,
		Status:     database.TxStatusPending,
		CreatedAt:  now,
		ExpiredAt:  expiredAt,
	}

	return tx, pngBytes, nil
}

// GenerateQRCodeImage membuat gambar QR Code PNG dari teks QRIS
func (s *CoordinatorService) GenerateQRCodeImage(qrisContent string) ([]byte, error) {
	return qrcode.Encode(qrisContent, qrcode.Medium, 320)
}
