@php
    $connectorTypes = ['CCS2', 'CHAdeMO', 'Type2', 'GB/T'];
@endphp

<div class="mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <label class="form-label mb-0"><strong>หัวชาร์จ</strong></label>
        <button type="button" class="btn btn-sm btn-primary" onclick="addConnectorRow()">
            <i class="bi bi-plus-circle me-1"></i>เพิ่ม
        </button>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered" style="background:#fff;">
            <thead class="table-light">
                <tr>
                    <th style="width:45%">ประเภทหัวชาร์จ</th>
                    <th style="width:35%">จำนวนหัว</th>
                    <th style="width:20%">ลบ</th>
                </tr>
            </thead>
            <tbody id="connectors-table-body">
                {{-- row template --}}
                <tr class="connector-row" data-index="0">
                    <td>
                        <select name="connectors[0][type]" class="form-select" required>
                            <option value="">-- เลือกประเภท --</option>
                            @foreach($connectorTypes as $type)
                                <option value="{{ $type }}">{{ $type }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        <input type="number" name="connectors[0][total]" class="form-control" min="1" value="1" required>
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger" onclick="removeConnectorRow(this)">ลบ</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <small class="text-muted">คุณสามารถเพิ่มหลายประเภทได้</small>
</div>

<script>
    function addConnectorRow() {
        const tbody = document.getElementById('connectors-table-body');
        const rows = tbody.querySelectorAll('.connector-row');
        const nextIndex = rows.length; // ใช้จำนวนแถวเป็น index

        const template = rows[0].cloneNode(true);
        template.dataset.index = nextIndex;

        // รีเซ็ตค่า
        const select = template.querySelector('select');
        const input = template.querySelector('input');
        select.value = '';
        input.value = 1;

        // เปลี่ยน name ของช่อง input/select
        // name เดิม: connectors[0][type] / connectors[0][total]
        select.name = select.name.replace(/connectors\[\d+\]\[type\]/, `connectors[${nextIndex}][type]`);
        input.name = input.name.replace(/connectors\[\d+\]\[total\]/, `connectors[${nextIndex}][total]`);

        tbody.appendChild(template);
    }

    function removeConnectorRow(btn) {
        const row = btn.closest('.connector-row');
        if (!row) return;
        row.remove();
    }
</script>

