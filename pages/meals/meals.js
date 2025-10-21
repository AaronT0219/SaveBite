function initializeCalendar() {
    const calendarEl = document.getElementById("calendar");

    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            right: 'prev,next today',
            center: 'title',
            left: ''
        },
        customButtons: {
            today: {
            text: '',
            click: function() {
                calendar.today();
            }
            }
        },
        initialView: "dayGridMonth",
        selectable: true,
        editable: true,
        eventColor: "#D4A373",
        eventDisplay: "block",
        events: [
            { title: "Team Meeting", start: "2025-10-21T14:30:00", allDay: false },
            { title: "Conference", start: "2025-10-25", end: "2025-10-27" }
        ],
        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
        },
        dateClick: function (info) {
            const title = prompt("Enter event title:");
            if (title) {
            calendar.addEvent({
                title: title,
                start: info.date,
                allDay: true
            });
            }
        }
    });

    calendar.render();

    const todayBtn = document.querySelector('.fc-today-button');
    if (todayBtn) {
        const iconEl = lucide.createElement(lucide.CalendarSync);
        todayBtn.appendChild(iconEl);
    }
}

function initMealsPage() {
    initializeCalendar();
}

window.initMealsPage = initMealsPage;