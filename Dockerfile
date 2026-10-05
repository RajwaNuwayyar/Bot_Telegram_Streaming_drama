# Tahap 1: Membangun aplikasi (Builder)
FROM golang:1.26-alpine AS builder
WORKDIR /app
COPY go.mod go.sum ./
RUN go mod download
COPY . .
RUN go build -o dramabot ./cmd/bot/main.go

# Tahap 2: Menjalankan aplikasi (Runner)
FROM alpine:latest
WORKDIR /app
COPY --from=builder /app/dramabot .
EXPOSE 8080
CMD ["./dramabot"]