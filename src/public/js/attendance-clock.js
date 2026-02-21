function updateDateTime() {
    var now = new Date();
    var year = now.getFullYear();
    var month = now.getMonth() + 1;
    var day = now.getDate();
    var dayNames = ['日', '月', '火', '水', '木', '金', '土'];
    var dayName = dayNames[now.getDay()];
    var hours = String(now.getHours()).padStart(2, '0');
    var minutes = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('current-date').textContent =
        year + '年' + month + '月' + day + '日(' + dayName + ')';
    document.getElementById('current-time').textContent =
        hours + ':' + minutes;
}

updateDateTime();
setInterval(updateDateTime, 1000);
