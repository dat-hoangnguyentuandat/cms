# Ghi chú vận hành Multi-tenant CMS

Cập nhật: 27/07/2026  
Source local: `E:\Project\cms`

## 1. Thông tin local hiện tại

### Platform Admin

- URL: http://localhost:8001/platform/login
- Tài khoản: `admin`
- Mật khẩu: `admin123`
- Email central: `admin@admin.com`
- Database central: `cms_mt_central`

> `admin123` chỉ dùng tạm ở local. Phải đổi sang mật khẩu mạnh trước khi mở
> Platform trên VPS.

### Tenant Default

- Website: http://127.0.0.1:8001
- Trang admin: http://127.0.0.1:8001/admin/login
- Tên tenant: `Default`
- UUID: `bef9fc6c-20c5-4550-b4c4-667f87ddae58`
- Domain local: `127.0.0.1`
- Database: `cms_mt_default`
- Theme được cấp và đang kích hoạt: `default`
- Plugin được cấp và đang kích hoạt: `default`
- Admin tenant: `admin`
- Mật khẩu admin tenant: `Default-admin-2026-pass`
- Email admin tenant: `admin@admin.com`

Platform dùng `localhost`, còn tenant dùng `127.0.0.1` để hai host được định
tuyến riêng mà không cần `nip.io` hoặc chỉnh file `hosts`.

## 2. Cấu hình `.env` local quan trọng

```dotenv
APP_URL=http://localhost:8001

TENANCY_ENABLED=true
TENANCY_CENTRAL_CONNECTION=central
TENANCY_ALLOW_PACKAGE_MANAGEMENT=false
PLATFORM_DOMAIN=localhost

CENTRAL_DB_CONNECTION=mysql
CENTRAL_DB_HOST=127.0.0.1
CENTRAL_DB_PORT=3306
CENTRAL_DB_DATABASE=cms_mt_central
CENTRAL_DB_USERNAME=root
CENTRAL_DB_PASSWORD=
```

Không còn tham chiếu `nip.io` trong source hoặc dữ liệu tenant.

## 3. Kiến trúc tenant

- Database central lưu Platform Admin, tenant, domain, package assignment và
  audit log.
- Mỗi tenant sử dụng database riêng.
- Source theme/plugin là catalog dùng chung.
- Tenant chỉ thấy theme/plugin đã được Platform cấp cho tenant đó.
- Một tenant có thể được cấp nhiều theme và nhiều plugin.
- Chỉ một theme được kích hoạt tại một thời điểm.
- Nhiều plugin có thể được kích hoạt đồng thời.
- Session, cache, storage và database được tách theo tenant.

## 4. Chức năng Platform

Platform hiện quản lý được:

- Tạo, sửa, tạm ngưng và mở lại tenant.
- Domain chính và domain phụ.
- Cấu hình database riêng.
- Kiểm tra kết nối, migrate và xóa cache tenant.
- Chạy seeder trên đúng database tenant.
- Tạo, sửa, đổi mật khẩu, khóa và mở khóa admin tenant.
- Cấp, kích hoạt và thu hồi nhiều theme/plugin.
- Upload hoặc cập nhật theme/plugin ZIP vào Catalog.
- Xóa theme/plugin khỏi Catalog.
- Quản lý Platform Admin.
- Xem audit log.

### Quy tắc xóa Catalog

- Package còn được cấp cho tenant sẽ không thể xóa.
- Phải thu hồi package khỏi tất cả tenant trước.
- Package có `0 tenant` mới được xóa source và public assets.
- Theme `default` luôn được bảo vệ.
- Backend kiểm tra lại số tenant sử dụng, không chỉ khóa nút trên giao diện.
- Mọi lần xóa thành công đều có audit log.

## 5. Seeder tenant

Platform tự quét các class kế thừa `Illuminate\Database\Seeder` trong:

```text
database/seeders
```

File seeder mới sẽ tự xuất hiện trên giao diện. Tên hiển thị là tên class, ví dụ:

```text
AcQuyNgocDieuSeeder
SixSpaSeeder
DatabaseSeeder
```

Seeder chạy trên database của tenant đang mở, không chạy trên tenant khác.
`DatabaseSeeder` hiện có tài khoản mặc định cũ `admin/admin123`, vì vậy cần cẩn
thận khi chạy trên production.

## 6. Tạo database khi tạo tenant

### SQLite

Nhập đường dẫn tương đối, ví dụ:

```text
tenants/customer.sqlite
```

Hệ thống tự tạo file trong `database/tenants/customer.sqlite` và chạy migration.
Không cần tạo file SQLite trước.

### MySQL/MariaDB

Trường Database là tên database, ví dụ:

```text
cms_customer
```

- Không chọn **Tự tạo database**: database phải tồn tại trước.
- Chọn **Tự tạo database**: tài khoản central chạy `CREATE DATABASE IF NOT EXISTS`.
- Chức năng này không tạo MySQL user và không chạy `GRANT`.
- Username/mật khẩu tenant phải có quyền truy cập database vừa tạo.

Production nên dùng database user riêng cho từng tenant, không dùng `root`.

## 7. Admin mặc định khi tạo tenant

Form tạo tenant tự điền:

- Username: `admin`
- Họ tên: `Administrator`
- Email: `admin@admin.com`
- Mật khẩu: tự sinh ngẫu nhiên 20 ký tự

Có checkbox hiện mật khẩu trước khi tạo. Sau khi tạo có thể sửa từng thông tin
trong trang chi tiết tenant; để trống mật khẩu mới thì giữ nguyên mật khẩu cũ.

Các giá trị mặc định có thể cấu hình bằng:

```dotenv
TENANCY_INITIAL_ADMIN_USERNAME=admin
TENANCY_INITIAL_ADMIN_NAME=Administrator
TENANCY_INITIAL_ADMIN_EMAIL=admin@admin.com
TENANCY_INITIAL_ADMIN_PASSWORD=
```

Để mật khẩu rỗng trong `.env` thì Platform tự sinh mật khẩu mạnh.

## 8. Chuẩn bị đưa lên VPS

1. Đổi `APP_ENV=production`, `APP_DEBUG=false`.
2. Giữ nguyên `APP_KEY`; đổi key sẽ làm hỏng dữ liệu mật khẩu database đã mã hóa.
3. Đổi `APP_URL` sang URL production.
4. Đổi `PLATFORM_DOMAIN` sang domain riêng, ví dụ `platform.example.com`.
5. Đổi domain tenant từ `127.0.0.1` sang domain thật của khách hàng.
6. Trỏ DNS Platform và tenant về VPS.
7. Cấu hình Nginx/Apache đưa mọi domain về cùng thư mục `public`.
8. Cấp HTTPS cho Platform và từng domain tenant.
9. Tạo database/user riêng, cấp quyền tối thiểu cần thiết.
10. Chạy central migration trước khi mở Platform.
11. Chạy migration từng tenant từ Platform.

Các thao tác deploy source, Composer, build frontend, backup/restore database và
central migration vẫn nên thực hiện ở cấp hệ thống. Vận hành tenant thông thường
thực hiện trên Platform.

## 9. Trạng thái dọn dữ liệu cũ

Đã xóa:

- Tenant Công ty A.
- Tenant Công ty B.
- Database `cms_mt_company_a`.
- Database `cms_mt_company_b`.

Hai nhóm thư mục storage cũ vẫn còn dạng orphan vì chính sách máy chặn xóa thư
mục đệ quy:

```text
storage/app/tenants/f5281a00-7eb3-469c-9188-4baf0e4f5c1d
storage/framework/tenants/f5281a00-7eb3-469c-9188-4baf0e4f5c1d
storage/app/tenants/cf810990-ec65-4b24-a69d-f819c24be118
storage/framework/tenants/cf810990-ec65-4b24-a69d-f819c24be118
```

Chúng không còn được tenant nào tham chiếu.

## 10. Kiểm thử gần nhất

```text
45 tests passed
247 assertions
```

Đã kiểm tra thực tế:

- Platform đăng nhập bằng `admin/admin123`.
- Platform và Catalog trả HTTP `200`.
- Danh sách tenant chỉ còn `Default`.
- Website Default trả HTTP `200`.
- Database cũ không còn; chỉ còn `cms_mt_default` trong nhóm database tenant.
- Không còn `nip.io`.
