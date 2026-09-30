@echo off
REM ============================================================
REM SCRIPT BACKUP HARIAN - Drama Bot Telegram Database
REM Letakkan file ini di server dan jadwalkan via Windows Task Scheduler
REM atau cron job (jika di Linux/VPS).
REM
REM KONFIGURASI:
REM   Sesuaikan variabel di bawah dengan environment Anda.
REM ============================================================

SET DB_HOST=localhost
SET DB_PORT=3306
SET DB_USER=drama_bot_user
SET DB_NAME=drama_bot_db
SET BACKUP_DIR=D:\Bot_Telegram_Streaming_drama\database\backups
SET MYSQLDUMP_PATH=mysqldump
REM Jika mysqldump tidak di PATH, gunakan path lengkap, contoh:
REM SET MYSQLDUMP_PATH="C:\Program Files\MySQL\MySQL Server 8.0\bin\mysqldump"

REM Buat folder backup jika belum ada
IF NOT EXIST "%BACKUP_DIR%" MKDIR "%BACKUP_DIR%"

REM Nama file backup dengan timestamp
FOR /F "tokens=2 delims==" %%I IN ('wmic os get LocalDateTime /value') DO SET DATETIME=%%I
SET TIMESTAMP=%DATETIME:~0,4%-%DATETIME:~4,2%-%DATETIME:~6,2%_%DATETIME:~8,2%%DATETIME:~10,2%%DATETIME:~12,2%
SET BACKUP_FILE=%BACKUP_DIR%\drama_bot_%TIMESTAMP%.sql
SET BACKUP_GZ=%BACKUP_FILE%.gz

REM Jalankan backup
echo [%DATE% %TIME%] Memulai backup database %DB_NAME%...

%MYSQLDUMP_PATH% ^
    --host=%DB_HOST% ^
    --port=%DB_PORT% ^
    --user=%DB_USER% ^
    --password ^
    --single-transaction ^
    --routines ^
    --events ^
    --triggers ^
    --add-drop-table ^
    --complete-insert ^
    --extended-insert ^
    --default-character-set=utf8mb4 ^
    %DB_NAME% > "%BACKUP_FILE%"

IF %ERRORLEVEL% EQU 0 (
    echo [%DATE% %TIME%] Backup berhasil: %BACKUP_FILE%
    REM Compress (opsional, butuh 7-Zip atau gzip di PATH)
    REM "C:\Program Files\7-Zip\7z.exe" a "%BACKUP_GZ%" "%BACKUP_FILE%" && DEL "%BACKUP_FILE%"
) ELSE (
    echo [%DATE% %TIME%] ERROR: Backup GAGAL! Periksa koneksi database.
    EXIT /B 1
)

REM Hapus backup yang lebih dari 30 hari
echo [%DATE% %TIME%] Membersihkan backup lama (> 30 hari)...
FORFILES /P "%BACKUP_DIR%" /S /M *.sql /D -30 /C "CMD /C DEL @PATH" 2>NUL
FORFILES /P "%BACKUP_DIR%" /S /M *.gz  /D -30 /C "CMD /C DEL @PATH" 2>NUL

echo [%DATE% %TIME%] Proses backup selesai.
