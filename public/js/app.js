// SweetAlert แทน confirm()
document.querySelectorAll('form[onsubmit]').forEach(form => {
    form.removeAttribute('onsubmit');
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: 'ไม่สามารถกู้คืนได้หลังจากลบแล้วครับ',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#2DC653',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'ลบเลย',
            cancelButtonText: 'ยกเลิก',
        }).then((result) => {
            if (result.isConfirmed) this.submit();
        });
    });
});