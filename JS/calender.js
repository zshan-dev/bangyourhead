document.addEventListener("DOMContentLoaded", function () {
    let calendarEl = document.getElementById("calendar");
    let serviceSelection = document.getElementById("serviceSelection");
    let removeEventModal = document.getElementById("removeEventModal");
    let confirmRemoveBtn = document.getElementById("confirmRemove");
    let cancelRemoveBtn = document.getElementById("cancelRemove");

    let selectedDate = null;
    let removeService = null; // Store the event to remove

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        editable: true,
        selectable: true,

        events: 'php/get_bookings.php', // Endpoint to get all bookings

        // Click date → Show service selection
        dateClick: function(info) {
            selectedDate = info.dateStr;
            serviceSelection.style.display = "block";
        },

        // Click event → Show booking details
        eventClick: function(info) {
            const bookingId = info.event.id;
            fetch(`php/get_booking_details.php?id=${bookingId}`)
                .then(response => response.json())
                .then(booking => {
                    // Build modal content based on booking details
                    let modalContent = document.querySelector('.modal-content');
                    let html = `
                        <h2>${booking.service_name}</h2>
                        <p><strong>Name:</strong> ${booking.name}</p>
                        <p><strong>Time:</strong> ${booking.time}</p>
                    `;

                    // Add sensitive details if allowed
                    if (booking.address && booking.phone) {
                        html += `
                            <p><strong>Address:</strong> ${booking.address}</p>
                            <p><strong>Phone:</strong> ${booking.phone}</p>
                        `;
                    }

                    // Add cancel button if allowed
                    if (booking.can_cancel) {
                        html += `
                            <button id="confirmRemove" class="danger-btn">Cancel Booking</button>
                            <button id="closeModal" class="secondary-btn">Close</button>
                        `;
                    } else {
                        html += `
                            <button id="closeModal" class="secondary-btn">Close</button>
                        `;
                    }

                    modalContent.innerHTML = html;

                    // Add event listeners for the new buttons
                    if (booking.can_cancel) {
                        document.getElementById('confirmRemove').addEventListener('click', function() {
                            removeBooking(bookingId);
                        });
                    }

                    document.getElementById('closeModal').addEventListener('click', function() {
                        removeEventModal.style.display = "none";
                    });

                    removeEventModal.style.display = "block";
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading booking details');
                });
        }
    });

    calendar.render();

    // Function to remove a booking
    function removeBooking(bookingId) {
        if (confirm('Are you sure you want to cancel this booking?')) {
            fetch('php/cancel_booking.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ booking_id: bookingId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    calendar.getEventById(bookingId).remove();
                    removeEventModal.style.display = "none";
                } else {
                    alert('Error canceling booking: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error canceling booking');
            });
        }
    }

    // Add event to calendar
    document.querySelectorAll(".service-option").forEach(button => {
        button.addEventListener("click", function() {
            if (selectedDate) {
                // Call add_booking.php instead of directly adding to calendar
                fetch('php/add_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        service: this.getAttribute("services"),
                        date: selectedDate
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        calendar.addEvent({
                            id: data.booking_id,
                            title: this.getAttribute("services"),
                            start: selectedDate
                        });
                        serviceSelection.style.display = "none";
                    } else {
                        alert('Error adding booking: ' + data.error);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error adding booking');
                });
            }
        });
    });

    // Close modals when clicking outside
    serviceSelection.addEventListener("click", function(event) {
        if (event.target === serviceSelection) {
            serviceSelection.style.display = "none";
        }
    });

    removeEventModal.addEventListener("click", function(event) {
        if (event.target === removeEventModal) {
            removeEventModal.style.display = "none";
        }
    });
});