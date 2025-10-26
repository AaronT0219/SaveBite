function initializeCalendar() {
    const calendarEl = document.getElementById("calendar");

    const calendar = new FullCalendar.Calendar(calendarEl, {
        headerToolbar: {
            right: 'prev,next today',
            center: 'title',
            left: 'dayGridMonth,timeGridWeek'
        },
        customButtons: {
            today: {
            text: '',
            click: function() {
                calendar.today();
            }
            }
        },
        views: {
            timeGridWeek: {
                expandRows: true,
                nowIndicator: true,
                allDaySlot: false
            }
        },
        initialView: "dayGridMonth",
        editable: true,
        eventColor: "#D4A373",
        eventDisplay: "block",
        events: [],
        eventTimeFormat: {
            hour: "2-digit",
            minute: "2-digit",
        },
        eventClick: function (info) {
            alert('Event: ' + info.event.title);
        }
    });

    calendar.render();

    const todayBtn = document.querySelector('.fc-today-button');
    if (todayBtn) {
        const iconEl = lucide.createElement(lucide.CalendarSync);
        todayBtn.appendChild(iconEl);
    }

    addMealEventListener(calendar);
}

function addMealEventListener(calendar) {
    // Open modal on "Add Meal" button click
    const addMealBtn = document.querySelector('.addMeal-btn');
    const addMealModal = new bootstrap.Modal(document.getElementById('addMealModal'));
    const mealForm = document.getElementById('mealForm');
    const mealConfirmBtn = document.getElementById('mealConfirm-btn');

    addMealBtn.addEventListener('click', () => {
        addMealModal.show();
    });

    // Handle form submission
    mealConfirmBtn.addEventListener('click', function (e) {
        console.log('confirm button clicked!!!');
        e.preventDefault();

        const title = document.getElementById('mealTitle').value.trim();
        const date = document.getElementById('mealDate').value;
        const desc = document.getElementById('mealDescription').value.trim();

        if (!title || !date) return;

        // Add event to FullCalendar
        calendar.addEvent({
            title: title,
            start: date,
            description: desc
        });

        // Close modal
        addMealModal.hide();
    });
}

function initMealsPage() {
    initializeCalendar();
}

window.initMealsPage = initMealsPage;