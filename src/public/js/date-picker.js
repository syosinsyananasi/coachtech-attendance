document.addEventListener('DOMContentLoaded', function () {
    var label = document.querySelector('label[for="date-picker"]');
    var picker = document.getElementById('date-picker');
    if (!label || !picker) return;

    label.addEventListener('click', function (e) {
        e.preventDefault();
        picker.showPicker();
    });
});
