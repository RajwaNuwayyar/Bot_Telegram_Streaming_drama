package payment

import (
	"crypto/rand"
	"encoding/hex"
	"fmt"
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
}

func NewPaymentCoordinator(apiURL, apiKey string) *CoordinatorService {
	return &CoordinatorService{
		apiURL: apiURL,
		apiKey: apiKey,
	}
}

// RequestQRIS membuat transaksi baru dan menghasilkan gambar QRIS
// Fungsi ini dirancang fleksibel: jika Payment Coordinator menyediakan API eksternal,
// kode HTTP request dapat diaktifkan di sini. Sementara itu, disediakan generator QRIS
// standar yang langsung menghasilkan QR Code PNG valid untuk pengujian.
func (s *CoordinatorService) RequestQRIS(telegramID int64, plan *database.VIPPlan) (*database.Transaction, []byte, error) {
	// Buat kode transaksi unik: TRX-<TIME>-<RANDOM>
	randomBytes := make([]byte, 3)
	_, _ = rand.Read(randomBytes)
	trxCode := fmt.Sprintf("TRX%d%s", time.Now().Unix()%100000, hex.EncodeToString(randomBytes))

	// Format standar QRIS payload (Dummy / Mock compliant untuk testing, atau dari gateway)
	qrisPayload := fmt.Sprintf("00020101021226680016ID.CO.QRIS.WWW0118DRAMABOTPAY9988770215%s520458125303360540%0.2f5802ID5913DRAMABOT VIP6007JAKARTA62070703A016304ABCD",
		trxCode, plan.Price)

	now := time.Now()
	expiredAt := now.Add(15 * time.Minute) // Waktu pembayaran 15 menit

	tx := &database.Transaction{
		TrxCode:    trxCode,
		TelegramID: telegramID,
		PlanID:     plan.ID,
		PlanName:   plan.Name,
		Amount:     plan.Price,
		QRISString: qrisPayload,
		Status:     database.TxStatusPending,
		CreatedAt:  now,
		ExpiredAt:  expiredAt,
	}

	// Generate QR Code PNG
	pngBytes, err := s.GenerateQRCodeImage(qrisPayload)
	if err != nil {
		return nil, nil, fmt.Errorf("gagal membuat gambar QRIS: %w", err)
	}

	return tx, pngBytes, nil
}

// GenerateQRCodeImage membuat gambar QR Code PNG dari teks QRIS
func (s *CoordinatorService) GenerateQRCodeImage(qrisContent string) ([]byte, error) {
	return qrcode.Encode(qrisContent, qrcode.Medium, 320)
}
