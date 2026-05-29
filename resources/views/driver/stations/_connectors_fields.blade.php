@php
    $connectorTypes = ['CCS2', 'CHAdeMO', 'Type2', 'GB/T'];

    if (filled(old('connectors'))) {
        $rows = array_values(old('connectors'));
    } elseif (!empty($connectorRows ?? null)) {
        $rows = array_values($connectorRows);
    } else {
        $rows = [];
    }

    if (empty($rows)) {
        $rows = [['type' => '', 'total' => 1]];
    }
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
                @foreach($rows as $idx => $row)
                    <tr class="connector-row" data-index="{{ $idx }}">
                        <td>
                            <select name="connectors[{{ $idx }}][type]" class="form-select" required>
                                <option value="">-- เลือกประเภท --</option>
                                @foreach($connectorTypes as $type)
                                    <option value="{{ $type }}"
                                        {{ ($row['type'] ?? '') === $type ? 'selected' : '' }}>
                                        {{ $type }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="number"
                                name="connectors[{{ $idx }}][total]"
                                class="form-control" min="1"
                                value="{{ $row['total'] ?? 1 }}" required>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-danger"
                                onclick="removeConnectorRow(this)">ลบ</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <small class="text-muted">คุณสามารถเพิ่ม/ลบหลายประเภทได้</small>
</div>

@once
    @push('scripts')
    <script>
        window.addConnectorRow = function () {
            var tbody = document.getElementById('connectors-table-body');
            var rows  = tbody.querySelectorAll('.connector-row');
            var idx   = rows.length;
            var clone = rows[0].cloneNode(true);
            clone.dataset.index = idx;
            var sel = clone.querySelector('select');
            var inp = clone.querySelector('input[type="number"]');
            sel.value = '';
            inp.value = 1;
            sel.name  = 'connectors[' + idx + '][type]';
            inp.name  = 'connectors[' + idx + '][total]';
            tbody.appendChild(clone);
        };

        window.removeConnectorRow = function (btn) {
            var tbody = document.getElementById('connectors-table-body');
            if (tbody.querySelectorAll('.connector-row').length <= 1) return;
            var row = btn.closest('.connector-row');
            if (row) row.remove();
        };
    </script>
    @endpush
@endonce
