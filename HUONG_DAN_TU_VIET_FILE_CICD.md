# Hướng Dẫn Tự Viết Tay File CI/CD Từ Con Số 0 (GitHub Actions)

Tài liệu này được thiết kế để giúp bạn hiểu rõ **bản chất từng dòng lệnh**, cú pháp YAML và tự tay viết được file cấu hình CI/CD cho riêng mình mà không cần phải copy-paste một cách máy móc.

---

## 1. Bản Chất Của File CI/CD Là Gì?

Hãy tưởng tượng GitHub cung cấp cho bạn một **chiếc máy tính ảo chạy Ubuntu (hoàn toàn miễn phí)** mỗi khi bạn push code. 

File CI/CD (định dạng `.yml`) thực chất là **bản danh sách hướng dẫn từng việc một** mà bạn giao cho chiếc máy tính ảo đó làm:
1. *"Bật máy lên."*
2. *"Tải code từ repo của tao về máy mày."*
3. *"Kiểm tra xem code PHP có bị gõ sai cú pháp không."*
4. *"Nếu không lỗi, hãy mở SSH kết nối vào VPS của tao và chạy lệnh `git pull`."*

---

## 2. Quy Tắc Vàng Khi Viết Cú Pháp YAML (`.yml`)

YAML rất dễ đọc vì nó giống tiếng Anh thông thường, nhưng có **2 quy tắc sống còn**:

1. **Thụt đầu dòng bằng DẤU CÁCH (Space), TUYỆT ĐỐI KHÔNG DÙNG PHÍM TAB**:
   - Mỗi cấp thụt vào đúng **2 dấu cách**.
   - Thụt sai cấp = Lỗi không chạy được.
2. **Dấu gạch ngang `-`**: Đại diện cho một phần tử trong danh sách (list).
3. **Cặp `Key: Value`**: Sau dấu hai chấm `:` **bắt buộc phải có 1 khoảng trắng** (Space).
   - Đúng: `name: Deploy`
   - Sai: `name:Deploy`

---

## 3. Cấu Trúc Xương Sống Của Một File CI/CD

Mọi file CI/CD của GitHub Actions đều tuân theo sơ đồ hình cây 4 tầng cơ bản sau:

```yaml
name: Tên của quy trình
on: Sự kiện kích hoạt (Khi nào chạy?)
jobs:
  tên_công_việc:
    runs-on: Hệ điều hành máy ảo
    steps:
      - name: Bước 1
        ...
      - name: Bước 2
        ...
```

---

## 4. Bắt Đầu Tự Viết Từng Khối Lệnh Từ Đầu Đến Cuối

Bây giờ chúng ta sẽ cùng viết file deploy PHP lên VPS từ tờ giấy trắng:

### Khối 1: Đặt tên quy trình (`name`)
Dòng này hiển thị tên đẹp mắt trên giao diện GitHub Actions để bạn dễ nhận biết:
```yaml
name: Quy Trinh CI CD Deploy PHP
```

---

### Khối 2: Khi nào thì chạy? (`on`)
Bạn muốn file này tự động chạy khi nào? Thường là khi có ai đó `push` code lên nhánh `main`:
```yaml
on:
  push:
    branches:
      - main
```
> **Mở rộng thêm**: Nếu bạn muốn có thêm nút bấm **chạy thủ công bằng tay** trên web GitHub, chỉ cần thêm dòng `workflow_dispatch:`
> ```yaml
> on:
>   push:
>     branches:
>       - main
>   workflow_dispatch: # Thêm dòng này để có nút bấm Run workflow thủ công
> ```

---

### Khối 3: Định nghĩa công việc (`jobs`)
Một quy trình có thể có nhiều job, ở đây chúng ta tạo một job có tên là `deploy`:
```yaml
jobs:
  deploy:
    name: Trien khai code len VPS
    runs-on: ubuntu-latest # Chọn máy ảo Ubuntu phiên bản mới nhất
```

---

### Khối 4: Danh sách các bước thực hiện (`steps`)
Trong job `deploy`, máy ảo sẽ thực thi tuần tự từng bước (`steps`). Mỗi bước bắt đầu bằng một dấu gạch ngang `-`:

#### Bước 4.1: Tải code từ GitHub về máy ảo (Checkout)
Để kiểm tra hoặc làm việc với code, máy ảo phải clone code về ổ đĩa của nó trước:
```yaml
    steps:
      - name: Bước 1 - Lay code tu repo ve
        uses: actions/checkout@v4
```
> **Giải thích từ khóa `uses`**: GitHub có một kho "chợ ứng dụng" (GitHub Marketplace) chứa những việc người khác đã viết sẵn. `actions/checkout@v4` là công cụ chính thức do chính GitHub viết để kéo code về. Thay vì tự viết lệnh git clone dài dòng, bạn chỉ cần gọi `uses: actions/checkout@v4`.

---

#### Bước 4.2 (Phần CI): Kiểm tra lỗi cú pháp PHP (Linting)
Trước khi đưa code lên server thật, ta cần kiểm tra xem có ai gõ thiếu dấu chấm phẩy `;` hay lỗi cú pháp nào không:
```yaml
      - name: Bước 2 - Cai dat moi truong PHP tren may ao
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2' # Chọn phiên bản PHP bạn muốn dùng để test

      - name: Bước 3 - Quet kiem tra cu phap PHP
        run: |
          find . -name "*.php" -not -path "./vendor/*" -exec php -l {} +
```
> **Giải thích từ khóa `run` và dấu `|`**: 
> - `run`: Dùng khi bạn muốn gõ lệnh Terminal (Bash/Linux) trực tiếp.
> - Dấu gạch đứng `|`: Cho phép bạn viết nhiều dòng lệnh bash liên tiếp bên dưới.
> - Lệnh `php -l <file>`: Lệnh của PHP dùng để kiểm tra lỗi cú pháp (Lint) mà không cần chạy code. Nếu có file lỗi, GitHub Actions sẽ báo đỏ và **dừng cuộc chơi ngay tại đây**, không deploy code lỗi lên VPS.

---

#### Bước 4.3 (Phần CD): Kết nối SSH vào VPS và kéo code về
Đây là bước quan trọng nhất để đưa code lên máy chủ thật của bạn. Ta sử dụng action nổi tiếng `appleboy/ssh-action`:

```yaml
      - name: Bước 4 - Ket noi SSH vao VPS de cap nhat code
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USERNAME }}
          key: ${{ secrets.VPS_SSH_KEY }}
          port: ${{ secrets.VPS_PORT || 22 }}
          script: |
            # 1. Nhay vao thu muc chua code tren VPS
            cd ${{ secrets.VPS_TARGET_DIR }} || exit 1
            
            # 2. Xoa bo cac thay doi rac (neu co) va keo code moi nhat ve
            git reset --hard HEAD
            git pull origin main
            
            # 3. Thong bao xong
            echo "Deploy thanh cong vao luc: $(date)"
```
> **Giải thích cú pháp `${{ secrets.TEN_BIEN }}`**:
> - Tuyệt đối **KHÔNG ĐƯỢC** điền trực tiếp IP VPS hay Private Key vào file này vì file này ai cũng đọc được nếu repo công khai.
> - Cú pháp `${{ secrets.XXX }}` bảo GitHub: *"Hãy lấy giá trị bảo mật từ kho bí mật (GitHub Secrets) mà tao đã cài sẵn trong mục Settings của Repo điền vào đây"*.

---

## 5. Ghép Lại Toàn Bộ File Hoàn Chỉnh

Dưới đây là toàn bộ những gì chúng ta vừa cùng nhau viết tay:

```yaml
name: Deploy PHP to VPS

on:
  push:
    branches:
      - main
  workflow_dispatch:

jobs:
  deploy:
    name: Trien khai code len VPS
    runs-on: ubuntu-latest

    steps:
      - name: 1. Checkout ma nguon
        uses: actions/checkout@v4

      - name: 2. Cai dat PHP 8.2
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: 3. Kiem tra loi cu phap PHP
        run: |
          find . -name "*.php" -not -path "./vendor/*" -exec php -l {} +

      - name: 4. SSH vao VPS va cap nhat code
        uses: appleboy/ssh-action@v1.0.3
        with:
          host: ${{ secrets.VPS_HOST }}
          username: ${{ secrets.VPS_USERNAME }}
          key: ${{ secrets.VPS_SSH_KEY }}
          port: ${{ secrets.VPS_PORT || 22 }}
          script: |
            cd ${{ secrets.VPS_TARGET_DIR }} || exit 1
            git reset --hard HEAD
            git pull origin main
            echo "Da deploy xong!"
```

---

## 6. Mẹo Tự Soạn Thảo & Tránh Lỗi Khi Mới Bắt Đầu

1. **Cài Extension trên VS Code**:
   - Cài extension **"GitHub Actions"** (của GitHub) và **"YAML"** (của Red Hat).
   - Khi bạn gõ sai thụt lề hoặc sai từ khóa, VS Code sẽ gạch chân đỏ báo lỗi ngay lập tức.
2. **Không tự gõ lại action phức tạp**:
   - Các thao tác như `checkout`, `ssh-action`, `setup-php`... hãy dùng từ khóa `uses` từ Marketplace vì cộng đồng đã tối ưu và xử lý bảo mật rất tốt.
   - Việc của bạn chủ yếu là viết đoạn kịch bản bash trong mục `script: |`.
3. **Thử nghiệm từng bước nhỏ**:
   - Đừng viết một mạch 100 dòng. Hãy viết bước 1 (checkout), push thử xem xanh không.
   - Tiếp tục thêm bước 2 (test lint), push tiếp.
   - Cuối cùng thêm bước SSH deploy. Làm như vậy nếu lỗi bạn sẽ biết ngay lỗi ở bước nào!
