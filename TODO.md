# TODO
- [ ] สำรวจไฟล์ blade ที่เกี่ยวข้องกับทุก role (layouts/app และทุกหน้าใน resources/views/**/)
- [ ] วางสเปค UI/UX ให้สอดคล้องกัน: spacing, typography, card/button/form/table/theme variables
- [ ] ปรับ `public/css/app.css` ให้เป็นชุดเดียว (แก้คลาสที่ซ้ำ/ทับกัน, จัด driver mobile-first ให้เสถียร)
- [ ] ปรับ `resources/views/layouts/app.blade.php` ให้มี spacing ป้องกันการทับกัน (bottom-nav / overlay / sheet)
- [ ] จัดหน้า driver เป็นอันดับ 1: `resources/views/driver/map.blade.php` (ย้าย style ที่ฝังในหน้าออกมาใช้ CSS, responsive sheet/floating actions)
- [ ] จัดหน้า driver: `resources/views/driver/station.blade.php`, `driver/favorites.blade.php`, `driver/account.blade.php`, `driver/stations/create.blade.php`, `driver/stations/edit.blade.php`
- [ ] จัดหน้า provider: dashboard/profile/stations/connectors (ใช้ card/form pattern เดียวกัน)
- [ ] จัดหน้า admin: dashboard/users/reviews/stations/pending/show (table/card/form responsive)
- [ ] ล้าง inline styles ที่ไม่จำเป็น/ซ้ำซ้อน ให้ใช้คลาสใน CSS
- [ ] ทดสอบ responsive หลายขนาดหน้าจอ + ตรวจว่า bottom-nav ไม่ชน
- [ ] ตรวจความคงที่: ไม่มี CSS/JS selector ที่หักพัง (โดยเฉพาะ map + modal)

