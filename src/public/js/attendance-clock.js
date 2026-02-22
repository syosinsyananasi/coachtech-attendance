function updateDateTime() {
    const now = new Date();
    const year = now.getFullYear();
    const month = now.getMonth() + 1;
    const day = now.getDate();
    const dayNames = ['日', '月', '火', '水', '木', '金', '土'];
    const dayName = dayNames[now.getDay()];
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');

    document.getElementById('current-date').textContent =
        year + '年' + month + '月' + day + '日(' + dayName + ')';
    document.getElementById('current-time').textContent =
        hours + ':' + minutes;
}

updateDateTime();
setInterval(updateDateTime, 1000);
