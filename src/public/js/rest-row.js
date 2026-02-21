document.addEventListener('DOMContentLoaded', function () {
    var container = document.getElementById('rest-container');
    var newRow = document.getElementById('rest-new-row');
    if (!container || !newRow) return;

    var currentIndex = parseInt(newRow.dataset.index);

    function watchRow(row) {
        var inputs = row.querySelectorAll('.detail-card__input');
        inputs.forEach(function (input) {
            input.addEventListener('input', function () {
                if (row.dataset.added) return;
                var hasValue = false;
                inputs.forEach(function (inp) {
                    if (inp.value.trim() !== '') hasValue = true;
                });
                if (hasValue) {
                    row.dataset.added = 'true';
                    currentIndex++;
                    addNewRow(currentIndex);
                }
            });
        });
    }

    function addNewRow(index) {
        var label = index > 0 ? '休憩' + (index + 1) : '休憩';
        var row = document.createElement('div');
        row.className = 'detail-card__row';
        row.dataset.index = index;
        row.innerHTML =
            '<span class="detail-card__label">' + label + '</span>' +
            '<div class="detail-card__value">' +
            '<input class="detail-card__input" type="text" name="rests[' + index + '][start]" value="">' +
            '<span class="detail-card__separator">〜</span>' +
            '<input class="detail-card__input" type="text" name="rests[' + index + '][end]" value="">' +
            '</div>';
        container.appendChild(row);
        watchRow(row);
    }

    watchRow(newRow);
});
