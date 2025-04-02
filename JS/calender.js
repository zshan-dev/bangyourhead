document.addEventListener("DOMContentLoaded", function () {
    let calendarEl = document.getElementById("calendar");
    let serviceSelection = document.getElementById("serviceSelection");
    let removeEventModal = document.getElementById("removeEventModal");
    let confirmRemoveBtn = document.getElementById("confirmRemove");
    let cancelRemoveBtn = document.getElementById("cancelRemove");

    let selectedDate = null;
    let eventToRemove = null; // Store the event to remove

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        editable: true,
        selectable: true,

        events: [
            { title: "Conference (placeholder)", start: "2025-04-05" }
        ],

        // Click date → Show service selection
        dateClick: function(info) {
            selectedDate = info.dateStr;
            serviceSelection.style.display = "block";
        },

        // Click event → Show removal modal
        eventClick: function(info) {
            eventToRemove = info.event; // Store event
            removeEventModal.style.display = "block";
        }
    });

    calendar.render();

    // Add event to calendar
    document.querySelectorAll(".service-option").forEach(button => {
        button.addEventListener("click", function() {
            if (selectedDate) {
                calendar.addEvent({
                    title: this.getAttribute("data-service"),
                    start: selectedDate
                });
                serviceSelection.style.display = "none";
            }
        });
    });

    // Confirm Remove Event
    confirmRemoveBtn.addEventListener("click", function() {
        if (eventToRemove) {
            eventToRemove.remove();
            eventToRemove = null;
            removeEventModal.style.display = "none";
        }
    });

    // Cancel Removal
    cancelRemoveBtn.addEventListener("click", function() {
        removeEventModal.style.display = "none";
        eventToRemove = null;
    });

    // Close modals when clicking outside
    serviceSelection.addEventListener("click", function(event) {
        if (event.target === serviceSelection){
            serviceSelection.style.display = "none";
        }
    });

    removeEventModal.addEventListener("click", function(event) {
        if (event.target === removeEventModal) {
            removeEventModal.style.display = "none";
        }
    });
});
