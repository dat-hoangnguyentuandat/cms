# Plugin Mặc định

Plugin mẫu chuẩn để copy khi tạo plugin CMS mới.

## Cấu trúc

```text
plugins/default/
├── plugin.json
├── admin_console.php
├── src/Plugin.php
├── routes/admin.php
├── routes/web.php
├── routes/api.php
├── database/migrations/
├── resources/views/
├── widgets/default-announcement/default-announcement.blade.php
└── assets/
```

## Quy tắc khi copy thành plugin mới

- Đổi `name`, `title`, `description`, `provider`, permission và route trong `plugin.json`.
- Đổi namespace trong `src/Plugin.php` theo provider mới.
- Widget phải khai báo `widgets.*.settings_schema` trong `plugin.json`.
- Asset đặt trong `assets/` và load bằng `plugin-assets/{slug}` qua partial asset.
- Admin route nằm trong `routes/admin.php`, tự được prefix `/admin/plugins/{slug}`.
- Frontend/API route chỉ khai báo khi cần, không sửa route core.
- Nếu plugin lớn, mở rộng `admin_console.php` theo schema `section/item/resource/settings/theme_block`.
- Migration nằm trong `database/migrations` và không xóa data khi deactivate.

Admin page mặc định chỉ hiển thị một dòng: `Đây là plugin mẫu`.
