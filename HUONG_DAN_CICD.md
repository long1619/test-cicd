# Hướng Dẫn CI/CD Tự Động Deploy Code PHP Lên VPS Qua SSH

Tài liệu này hướng dẫn từng bước từ A đến Z cách thiết lập quy trình **CI/CD** bằng **GitHub Actions** để tự động kiểm tra cú pháp và deploy ứng dụng PHP lên **VPS** thông qua **SSH** khi bạn push code lên repo `https://github.com/long1619/test-cicd.git`.

---

## 1. Mô Hình Hoạt Động (Architecture Flow)

```
[Máy tính của bạn (Local)]
          │
          │ git push origin main
          ▼
[GitHub Repository]
          │
          │ Kích hoạt GitHub Actions Workflow (.github/workflows/deploy.yml)
          ▼
[GitHub Actions Runner (Ubuntu)]
   ├─ 1. Checkout mã nguồn
   ├─ 2. Kiểm tra lỗi cú pháp PHP (Linting)
   └─ 3. Kết nối SSH vào VPS bằng Private Key bí mật (GitHub Secrets)
          │
          ▼ SSH Run Script
[Máy chủ VPS]
   ├─ Di chuyển vào thư mục code (/var/www/test-cicd)
   ├─ Chạy: git pull origin main
   └─ Khởi động lại hoặc reload dịch vụ PHP/Web server (nếu cần)
```

---

## 2. Bước 1: Chuẩn Bị Trên Máy Cục Bộ (Local)

Thư mục dự án của bạn hiện đã có sẵn các file:
- [index.php](file:///e:/D%E1%BB%B1%20%C3%A1n%20c%C3%A1%20nh%C3%A2n%202026/test-cicd/index.php): File mã nguồn PHP mẫu hiển thị thông tin máy chủ và trạng thái deploy.
- [.github/workflows/deploy.yml](file:///e:/D%E1%BB%B1%20%C3%A1n%20c%C3%A1%20nh%C3%A2n%202026/test-cicd/.github/workflows/deploy.yml): File kịch bản chạy tự động của GitHub Actions.
- [.gitignore](file:///e:/D%E1%BB%B1%20%C3%A1n%20c%C3%A1%20nh%C3%A2n%202026/test-cicd/.gitignore): Loại bỏ các file tạm thời, vendor, .env.

### Khởi tạo Git và liên kết với GitHub

Mở Terminal (PowerShell hoặc Git Bash) tại thư mục dự án và chạy các lệnh:

```bash
# 1. Khởi tạo kho chứa git cục bộ
git init -b main

# 2. Thêm tất cả file vào staging
git add .

# 3. Tạo commit đầu tiên
git commit -m "feat: initial commit with php demo and github actions"

# 4. Gắn remote repository của bạn
git remote add origin https://github.com/long1619/test-cicd.git
```
*(Chưa cần push vội, hãy chuẩn bị VPS và GitHub Secrets trước để lần push đầu tiên chạy thành công ngay lập tức)*.

---

## 3. Bước 2: Chuẩn Bị Trên Máy Chủ VPS

Đăng nhập vào VPS của bạn thông qua SSH bằng Terminal:
```bash
ssh root@<IP_VPS_CUA_BAN>
```

### 2.1. Cài đặt môi trường (nếu VPS mới tinh)
Đảm bảo VPS đã có Git, Web Server (Nginx hoặc Apache) và PHP:
```bash
# Cập nhật package (Ubuntu/Debian)
sudo apt update && sudo apt upgrade -y

# Cài git, nginx và php
sudo apt install -y git nginx php-fpm php-cli
```

### 2.2. Tạo SSH Key Pair dành riêng cho GitHub Actions
Khuyến khích tạo một cặp khóa SSH riêng biệt để cấp quyền truy cập cho GitHub Actions:

```bash
# Chạy trên VPS để tạo key (không đặt passphrase, bấm Enter hết)
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/github_actions_id

# Thêm khóa công khai (Public Key) vào danh sách ủy quyền của VPS
cat ~/.ssh/github_actions_id.pub >> ~/.ssh/authorized_keys

# Đảm bảo phân quyền chính xác cho thư mục SSH
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

> [!IMPORTANT]
> Hãy in nội dung **Private Key** vừa tạo ra màn hình và sao chép lại toàn bộ (bao gồm cả dòng `-----BEGIN OPENSSH PRIVATE KEY-----` và `-----END OPENSSH PRIVATE KEY-----`):
> ```bash
> cat ~/.ssh/github_actions_id
> ```
> Bạn sẽ dán đoạn này vào GitHub Secrets ở **Bước 3**.

### 2.3. Tạo thư mục chứa code và Clone repo lần đầu
Tạo thư mục trên VPS (ví dụ: `/var/www/test-cicd`):

```bash
# Tạo thư mục
sudo mkdir -p /var/www/test-cicd

# Cấp quyền sở hữu cho user hiện tại (ví dụ root hoặc user bạn dùng)
sudo chown -R $USER:$USER /var/www/test-cicd

# Di chuyển vào thư mục
cd /var/www/test-cicd

# Clone repo về lần đầu tiên
# Lưu ý: Nếu repo là Public:
git clone https://github.com/long1619/test-cicd.git .

# (Nếu repo là Private, bạn cần thêm SSH Deploy Key trên GitHub để VPS có quyền kéo code, hoặc dùng Personal Access Token).
```

### 2.4. Cấu hình Nginx / Apache trỏ vào thư mục
Cấu hình Webserver (ví dụ Nginx) trỏ `root` về `/var/www/test-cicd` để khi người dùng truy cập IP của VPS sẽ mở file `index.php`.

Ví dụ file cấu hình Nginx tối thiểu (`/etc/nginx/sites-available/test-cicd`):
```nginx
server {
    listen 80;
    server_name _; # Hoặc IP VPS / domain của bạn

    root /var/www/test-cicd;
    index index.php index.html;

    location / {
        try_files $uri $uri/ =404;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # Chỉnh đúng phiên bản PHP của bạn
    }
}
```
Kích hoạt và reload Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/test-cicd /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## 4. Bước 3: Cấu Hình GitHub Secrets

GitHub Actions cần các thông số kết nối vào VPS nhưng không được để lộ công khai trong code. Chúng ta sẽ lưu vào **GitHub Secrets**.

1. Truy cập repo trên trình duyệt: `https://github.com/long1619/test-cicd`
2. Vào **Settings** -> mục bên trái chọn **Secrets and variables** -> chọn **Actions**.
3. Bấm nút **New repository secret** để thêm lần lượt 5 secret sau:

| Tên Secret | Ý Nghĩa / Giá Trị Mẫu |
| :--- | :--- |
| `VPS_HOST` | Địa chỉ IP Public của VPS (Ví dụ: `103.123.45.67`) |
| `VPS_USERNAME` | User SSH bạn dùng trên VPS (Ví dụ: `root` hoặc `ubuntu`) |
| `VPS_SSH_KEY` | Toàn bộ nội dung **Private Key** bí mật (xem chi tiết cách lấy bên dưới) |
| `VPS_PORT` | Cổng SSH của VPS (Mặc định là `22`) |
| `VPS_TARGET_DIR` | Đường dẫn tuyệt đối đến thư mục code trên VPS: `/var/www/test-cicd` |

> [!IMPORTANT]
> ### Hướng dẫn chi tiết cách lấy `VPS_SSH_KEY`:
> 1. **Bản chất**: Đây là **Private Key (Chìa khóa bí mật)**, tuyệt đối **KHÔNG** dùng file `.pub` (Public Key).
> 2. **Lệnh in Private Key trên VPS**:
>    - Nếu bạn tạo key theo bước 2.2:
>      ```bash
>      cat ~/.ssh/github_actions_id
>      ```
>    - Nếu bạn tạo key mặc định (`ssh-keygen -t ed25519`):
>      ```bash
>      cat ~/.ssh/id_ed25519
>      ```
> 3. **Quy tắc Copy**: Bạn phải copy **toàn bộ nội dung**, bao gồm cả dòng đầu và dòng cuối:
>    ```text
>    -----BEGIN OPENSSH PRIVATE KEY-----
>    ... (toàn bộ các dòng ký tự ở giữa) ...
>    -----END OPENSSH PRIVATE KEY-----
>    ```
> 4. **Điều kiện để VPS nhận chìa khóa**: Trước đó trên VPS, bạn phải đảm bảo đã đưa Public Key tương ứng vào danh sách ủy quyền:
>    ```bash
>    cat ~/.ssh/id_ed25519.pub >> ~/.ssh/authorized_keys
>    chmod 600 ~/.ssh/authorized_keys
>    ```


---

## 5. Bước 4: Đẩy Code Lên GitHub & Tận Hưởng Thành Quả

Sau khi đã cấu hình xong Secrets:

1. Quay lại terminal máy local, đẩy code lên nhánh `main`:
   ```bash
   git push -u origin main
   ```

2. Truy cập tab **Actions** trên GitHub repo:
   - Bạn sẽ thấy một workflow có tên `Deploy PHP to VPS` đang chạy.
   - Bấm vào để xem log chi tiết từng bước:
     - `Kiểm tra cú pháp PHP (Lint Check)`
     - `Deploy qua SSH lên VPS`: Đăng nhập SSH vào VPS và thực thi lệnh `git pull origin main`.

3. Mở trình duyệt và truy cập vào IP của VPS: `http://<IP_VPS_CUA_BAN>/`
   - Bạn sẽ thấy giao diện dashboard test PHP màu tối hiện đại, hiển thị PHP Version và thông báo **CI/CD Deployment Thành Công**!

---

## 6. Trải Nghiệm Tự Động Hóa (Test Thay Đổi)

Để kiểm chứng tính năng tự động:
1. Mở file [index.php](file:///e:/D%E1%BB%B1%20%C3%A1n%20c%C3%A1%20nh%C3%A2n%202026/test-cicd/index.php).
2. Sửa dòng `<span class="value">v1.0.0</span>` thành `<span class="value">v1.0.1</span>`.
3. Commit và push:
   ```bash
   git add index.php
   git commit -m "bump: update version to v1.0.1"
   git push
   ```
4. Đợi khoảng 15-30 giây, F5 lại trang web trên trình duyệt: Version sẽ tự động đổi sang `v1.0.1` mà bạn không cần phải đăng nhập vào VPS để làm gì cả!

---

## 7. Các Vấn Đề Thường Gặp (Troubleshooting)

- **Lỗi `Host key verification failed`**: Action `appleboy/ssh-action` tự động xử lý known_hosts, nhưng nếu gặp lỗi, hãy kiểm tra lại `VPS_HOST` và `VPS_PORT`.
- **Lỗi `Permission denied (publickey)`**: Khóa `VPS_SSH_KEY` trên GitHub Secrets chưa khớp với Public Key trong `~/.ssh/authorized_keys` trên VPS, hoặc quyền file `~/.ssh/authorized_keys` bị sai (chạy `chmod 600 ~/.ssh/authorized_keys`).
- **Lỗi `error: Your local changes to the following files would be overwritten by merge`**: Trong file workflow đã có lệnh `git reset --hard HEAD` để đảm bảo code trên VPS luôn đồng bộ tuyệt đối với GitHub. Tránh sửa code trực tiếp trên VPS.
