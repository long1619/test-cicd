# Test CI/CD PHP Deployment to VPS via SSH

Dự án mẫu thực hành thiết lập quy trình **CI/CD** tự động kiểm tra cú pháp và deploy mã nguồn PHP lên máy chủ VPS thông qua kết nối SSH sử dụng **GitHub Actions**.

## Cấu trúc thư mục

```
.
├── .github/
│   └── workflows/
│       └── deploy.yml        # Kịch bản GitHub Actions tự động kiểm tra và deploy qua SSH
├── index.php                 # Mã nguồn PHP demo giao diện theo dõi trạng thái deploy
├── .gitignore                # Bỏ qua các file tạm thời, vendor, env
├── HUONG_DAN_CICD.md         # Hướng dẫn chi tiết từng bước từ A đến Z
└── README.md                 # Giới thiệu dự án
```

## Hướng dẫn chi tiết

Xem toàn bộ các bước thiết lập VPS, tạo SSH Key, cấu hình GitHub Secrets và thực hiện deploy tại file:
👉 **[HUONG_DAN_CICD.md](HUONG_DAN_CICD.md)**

## Quy trình tóm tắt

1. **Khởi tạo repo trên GitHub**: `https://github.com/long1619/test-cicd.git`
2. **Cấu hình trên VPS**: Tạo SSH Key pair, thêm public key vào `~/.ssh/authorized_keys`, clone repo về thư mục `/var/www/test-cicd`.
3. **Thêm GitHub Secrets**:
   - `VPS_HOST`: IP của VPS
   - `VPS_USERNAME`: User SSH (ví dụ: `root` hoặc `ubuntu`)
   - `VPS_SSH_KEY`: Nội dung Private Key của VPS
   - `VPS_PORT`: Cổng SSH (mặc định 22)
   - `VPS_TARGET_DIR`: Thư mục dự án trên VPS (`/var/www/test-cicd`)
4. **Push code lên GitHub**: GitHub Actions sẽ tự động kích hoạt, lint code và SSH vào VPS để cập nhật code mới nhất.
