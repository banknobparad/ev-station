# TODO_TRIP (Driver Map -> Trip)

## Navbar / Menu
- [x] เปลี่ยน bottom-nav จาก Saved -> Trip ใน `resources/views/layouts/app.blade.php`


## หน้า Map (driver/map.blade.php)
- [x] ลบ/ซ่อนปุ่ม Favorites ใน overlay search row (ตามโจทย์ให้เอา Save/Favorites ออก)

- [ ] เพิ่ม UI กล่อง Trip: From (GPS or input) + To (input) + ปุ่ม "ค้นหาเส้นทาง"
- [ ] เพิ่ม UI แผงผลการดูเส้นทาง: แสดง From->To, ระยะทาง และปุ่ม X ปิดการดูเส้นทาง
- [ ] เพิ่ม state/layer ใหม่สำหรับผล Trip เพื่อไม่ไปรบกวน markers ปกติ

## Logic ฝั่ง Client
- [ ] geocode: ถ้า From เป็น GPS ใช้พิกัดจาก navigator.geolocation; ถ้าเป็นข้อความใช้ Nominatim
- [ ] routing: เรียก OSRM เพื่อขอ geometry/ระยะทาง (polyline)
- [ ] Draw route: วาด polyline บน Leaflet
- [ ] Buffer zone search: คำนวณสถานีที่อยู่ห่างจากแนวเส้นทางไม่เกิน 10km แล้วปักหมุด
- [ ] เคลียร์ผล Trip เมื่อกด X

## ทดสอบ
- [ ] ทดสอบว่าเมนู Trip เปิดหน้า map เดิม
- [ ] ทดสอบเส้นทาง + buffer + ปักหมุด

