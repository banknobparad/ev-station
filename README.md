# EV Station

ระบบจัดการสถานีชาร์จรถยนต์ไฟฟ้า (โปรเจกต์จบ) — รองรับ 3 บทบาท: **Driver**, **Provider**, **Admin**

## ความต้องการของระบบ

- PHP 8.2+
- Composer
- Node.js 18+ (สำหรับ frontend assets)
- SQLite / MySQL / MariaDB

## ติดตั้ง

```bash
# 1. ติดตั้ง dependencies
composer install
npm install

# 2. ตั้งค่า environment
cp .env.example .env
php artisan key:generate

# 3. ฐานข้อมูล (SQLite ตามค่า default)
# สร้างไฟล์ database/database.sqlite หากยังไม่มี
php artisan migrate

# 4. ลิงก์ storage สำหรับรูปภาพ
php artisan storage:link

# 5. Seed บัญชี Admin (และ demo ถ้าต้องการ)
php artisan db:seed
# หรือเฉพาะ admin:
php artisan db:seed --class=AdminSeeder

# 6. Build assets (development)
npm run dev
# หรือ production:
npm run build

# 7. รันเซิร์ฟเวอร์
php artisan serve
```

เปิดเบราว์เซอร์ที่ `http://127.0.0.1:8000`

## บัญชีทดสอบ

| บทบาท | วิธีเข้าใช้ | หมายเหตุ |
|--------|------------|----------|
| **Admin** | `/login` → เลือก Admin → `admin@ev.com` / `password` | จาก `AdminSeeder` |
| **Driver** | `/register` หรือ `/login` ด้วยเบอร์โทร | OTP จำลองสำหรับทดลอง (ไม่ส่ง SMS จริง) |
| **Provider** | สร้างจาก Admin หรือ seed `DemoUserSeeder` | `provider@ev.com` / `password` |

```bash
# seed บัญชี demo เพิ่ม (driver + provider)
php artisan db:seed --class=DemoUserSeeder
```

## Flow ตามบทบาท

### Driver (คนขับรถ)
1. สมัคร/เข้าสู่ระบบด้วยเบอร์โทร + OTP จำลอง
2. ดูแผนที่สถานีที่ **อนุมัติแล้ว** (`/map`)
3. เพิ่มสถานีใหม่ → สถานะ **รออนุมัติ** จนกว่า Admin จะอนุมัติ
4. แก้ไข/ขอลบสถานีของตัวเอง → ส่งคำขอให้ Admin (ข้อมูลยังไม่เปลี่ยนจนกว่าจะอนุมัติ)
5. รีวิว, รายการโปรด

### Provider (ผู้ให้บริการสถานี)
1. เข้าสู่ระบบด้วย email/password
2. จัดการสถานีของตัวเอง (เพิ่ม/แก้/ลบ) — **ขึ้นแผนที่ทันที** (approved)
3. จัดการหัวชาร์จแยกตามสถานี

### Admin
1. อนุมัติ/ปฏิเสธสถานีใหม่จาก Driver
2. อนุมัติ/ยกเลิกคำขอ **แก้ไข/ลบ** สถานีจาก Driver (ดู diff ใน modal)
3. จัดการสถานีทั้งหมด, ผู้ใช้, รีวิว

## โครงสร้างโค้ดหลัก

```
app/
  Http/
    Controllers/     # แยกตาม Driver / Provider / Admin
    Requests/Station/  # Form Request สำหรับ validate สถานี
  Services/
    StationService.php  # logic สถานี, หัวชาร์จ, audit log
```

## หมายเหตุโปรเจกต์จบ

- OTP เป็นแบบทดลอง ไม่ส่ง SMS จริง
- ไม่มีระบบแจ้งเตือน push/email
- รูปภาพเก็บที่ `storage/app/public/stations`
