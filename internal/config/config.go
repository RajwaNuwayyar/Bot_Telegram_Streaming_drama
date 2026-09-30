package config

import (
	"log"
	"os"
	"strconv"

	"github.com/joho/godotenv"
)

type Config struct {
	BotToken          string
	BotUsername       string
	PrivateChannelID  int64
	WebAppURL         string
	OfficialGroupURL  string
	AdminUserID       int64
	ServerPort        string
	DatabasePath      string
	PaymentWebhookURL string
}

func LoadConfig() *Config {
	// Muat file .env jika ada
	if err := godotenv.Load(); err != nil {
		log.Println("[Config] File .env tidak ditemukan, menggunakan environment variable sistem.")
	}

	channelID, _ := strconv.ParseInt(getEnv("PRIVATE_CHANNEL_ID", "0"), 10, 64)
	adminID, _ := strconv.ParseInt(getEnv("ADMIN_USER_ID", "0"), 10, 64)

	cfg := &Config{
		BotToken:          getEnv("BOT_TOKEN", ""),
		BotUsername:       getEnv("BOT_USERNAME", "@TreadLessBot"),
		PrivateChannelID:  channelID,
		WebAppURL:         getEnv("WEB_APP_URL", "https://t.me/dailydramabot/app"),
		OfficialGroupURL:  getEnv("OFFICIAL_GROUP_URL", "https://t.me/dailydrama_official"),
		AdminUserID:       adminID,
		ServerPort:        getEnv("SERVER_PORT", "8080"),
		DatabasePath:      getEnv("DATABASE_PATH", "dramabot.db"),
		PaymentWebhookURL: getEnv("PAYMENT_WEBHOOK_URL", "http://localhost:8080/api/payment/webhook"),
	}

	return cfg
}

func getEnv(key, defaultVal string) string {
	if val, exists := os.LookupEnv(key); exists && val != "" {
		return val
	}
	return defaultVal
}
