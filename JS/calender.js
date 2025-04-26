/**
 * Calendar Management System
 * 
 * @author  Seif Otefa (400557672) and Faaz Shaikh (400595663)
 * @date 2024-04-25
 * @description Handles the calendar interface for booking management including:
 * - Displaying and managing service bookings
 * - Creating new bookings
 * - Viewing booking details
 * - Canceling bookings (for admins and booking owners)
 */

document.addEventListener("DOMContentLoaded", function () {
    let calendarEl = document.getElementById("calendar");
    let serviceSelection = document.getElementById("serviceSelection");
    let removeEventModal = document.getElementById("removeEventModal");

    // Ensure proper modal structure exists
    if (!removeEventModal.querySelector('.modal-content')) {
        removeEventModal.innerHTML = '<div class="modal-content"></div>';
    }

    let selectedDate = null;
    let removeService = null;

    let calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: "dayGridMonth",
        headerToolbar: {
            left: "prev,next today",
            center: "title",
            right: "dayGridMonth,timeGridWeek,timeGridDay",
        },
        editable: false,
        selectable: true,

        events: 'php/get_bookings.php',

        /**
         * Handles date click events to show service selection
         * @param {Object} info - FullCalendar date click info
         */
        dateClick: function(info) {
            selectedDate = info.dateStr;
            
            // Get existing bookings for the selected date
            fetch(`php/get_bookings.php?date=${selectedDate}`)
                .then(response => response.json())
                .then(bookings => {
                    let modalContent = document.querySelector('.modal-content');
                    modalContent.innerHTML = `
                        <h2>New Booking</h2>
                        <div class="form-group">
                            <label for="bookingTime">Select Time:</label>
                            <select id="bookingTime" required>
                                <option value="">Choose a time</option>
                                ${generateTimeOptions(9, 17, bookings, selectedDate)}
                            </select>
                            <div id="timeError" class="error-message"></div>
                        </div>
                        <div class="service-buttons">
                            <button class="service-option btn" services="Window Washing">Window Washing</button>
                            <button class="service-option btn" services="Power Washing">Power Washing</button>
                            <button class="service-option btn" services="Fence restoration">Fence Restoration</button>
                            <button class="service-option btn" services="Gutter Cleaning">Gutter Cleaning</button>
                            <button class="service-option btn" services="Deck Restoration">Deck Restoration</button>
                        </div>
                        <button id="closeModal" class="secondary-btn">Cancel</button>
                    `;

                    // Add event listeners
                    document.querySelectorAll(".service-option").forEach(button => {
                        button.addEventListener("click", function() {
                            const selectedTime = document.getElementById('bookingTime').value;
                            const errorDiv = document.getElementById('timeError');
                            
                            if (!selectedTime) {
                                errorDiv.textContent = 'Please select a time first';
                                return;
                            }

                            errorDiv.textContent = ''; // Clear any previous error
                            addBooking(this.getAttribute("services"), selectedDate, selectedTime);
                        });
                    });

                    document.getElementById('closeModal').addEventListener('click', function() {
                        removeEventModal.style.display = "none";
                    });

                    removeEventModal.style.display = "block";
                })
                .catch(error => {
                    console.error('Error:', error);
                    const errorDiv = document.getElementById('timeError');
                    errorDiv.textContent = 'Error loading available times';
                    errorDiv.className = 'error-message';
                });
        },

        /**
         * Handles event click to show booking details
         * @param {Object} info - FullCalendar event click info
         */
        eventClick: function(info) {
            const bookingId = info.event.id;
            fetch(`php/get_booking_details.php?id=${bookingId}`)
                .then(response => response.json())
                .then(booking => {
                    console.log('Booking details:', booking); // Add debug logging
                    let modalContent = document.querySelector('.modal-content');
                    let html = `
                        <div class="booking-details">
                            <h2>${booking.service_name}</h2>
                            <div class="details-grid">
                                <p><strong>Date:</strong> ${formatDate(booking.date)}</p>
                                <p><strong>Time:</strong> ${formatTime(booking.start_time)} - ${formatTime(booking.end_time)}</p>
                                <p><strong>Client:</strong> ${booking.name}</p>
                                <p><strong>Email:</strong> ${booking.email}</p>
                                <p><strong>Address:</strong> ${booking.address}</p>
                            </div>
                            <div id="bookingFeedback" class="feedback-message"></div>
                            <div class="modal-buttons">
                                ${(booking.is_admin || booking.can_cancel) ? 
                                    `<button id="confirmRemove" class="danger-btn">Cancel Booking</button>` : ''}
                                <button id="closeModal" class="secondary-btn">Close</button>
                            </div>
                        </div>
                    `;

                    modalContent.innerHTML = html;

                    if (booking.is_admin || booking.can_cancel) {
                        document.getElementById('confirmRemove').addEventListener('click', function() {
                            const feedbackDiv = document.getElementById('bookingFeedback');
                            feedbackDiv.textContent = 'Cancelling booking...';
                            feedbackDiv.className = 'feedback-message pending';
                            
                            removeBooking(bookingId, feedbackDiv);
                        });
                    }

                    document.getElementById('closeModal').addEventListener('click', function() {
                        removeEventModal.style.display = "none";
                    });

                    removeEventModal.style.display = "block";
                })
                .catch(error => {
                    console.error('Error:', error);
                    const feedbackDiv = document.getElementById('bookingFeedback');
                    feedbackDiv.textContent = 'Error loading booking details';
                    feedbackDiv.className = 'feedback-message error';
                });
        }
    });

    calendar.render();

    /**
     * Converts a date string into a human-readable format
     *
     * @param {String} dateStr The date to format (YYYY-MM-DD)
     * @returns {String} Formatted date string like "Monday, April 25, 2024"
     */
    function formatDate(dateStr) {
        if (!dateStr) return 'No date specified';
        // Split the date string if it contains a time component
        const datePart = dateStr.split('T')[0];
        const date = new Date(datePart);
        if (isNaN(date.getTime())) return 'Invalid Date';
        return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    /**
     * Converts a time string into 12-hour format
     *
     * @param {String} timeStr The time to format (HH:MM:SS)
     * @returns {String} Formatted time string like "2:00 PM"
     */
    function formatTime(timeStr) {
        if (!timeStr) return 'No time specified';
        try {
            const [hours, minutes] = timeStr.split(':');
            const date = new Date();
            date.setHours(parseInt(hours, 10));
            date.setMinutes(parseInt(minutes, 10));
            return date.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit'
            });
        } catch (e) {
            return 'Invalid Time';
        }
    }

    /**
     * Creates a new service booking
     *
     * @param {String} serviceName The type of service being booked
     * @param {String} date The date for the booking (YYYY-MM-DD)
     * @param {String} time The time slot for the booking (HH:MM)
     * @returns {void}
     */
    function addBooking(serviceName, date, time) {
        const feedbackDiv = document.getElementById('timeError');
        feedbackDiv.className = 'feedback-message pending';
        feedbackDiv.textContent = 'Creating booking...';

        fetch('php/add_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                service_name: serviceName,
                date: date,
                time: time
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                removeEventModal.style.display = "none";
                calendar.refetchEvents();
            } else {
                feedbackDiv.textContent = data.error;
                feedbackDiv.className = 'feedback-message error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackDiv.textContent = 'Error creating booking';
            feedbackDiv.className = 'feedback-message error';
        });
    }

    /**
     * Cancels an existing booking
     *
     * @param {Number} bookingId The ID of the booking to cancel
     * @param {HTMLElement} feedbackDiv Element to show status messages
     * @returns {void}
     */
    function removeBooking(bookingId, feedbackDiv) {
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
                feedbackDiv.textContent = 'Booking cancelled successfully';
                feedbackDiv.className = 'feedback-message success';
                setTimeout(() => {
                    removeEventModal.style.display = "none";
                    calendar.refetchEvents();
                }, 1500);
            } else {
                feedbackDiv.textContent = data.error;
                feedbackDiv.className = 'feedback-message error';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            feedbackDiv.textContent = 'Error cancelling booking';
            feedbackDiv.className = 'feedback-message error';
        });
    }

    // Close modals when clicking outside
    removeEventModal.addEventListener("click", function(event) {
        if (event.target === removeEventModal) {
            removeEventModal.style.display = "none";
        }
    });

    /**
     * Creates a list of available booking time slots
     *
     * @param {Number} startHour First available hour (0-23)
     * @param {Number} endHour Last available hour (0-23)
     * @param {Array} existingBookings List of current bookings to check conflicts
     * @param {String} selectedDate Date to check availability (YYYY-MM-DD)
     * @returns {String} HTML string of time slot options
     */
    function generateTimeOptions(startHour, endHour, existingBookings, selectedDate) {
        let options = [];
        let bookedTimes = existingBookings.map(booking => {
            return {
                start: new Date(`${booking.date}T${booking.start_time}`).getTime(),
                end: new Date(`${booking.date}T${booking.end_time}`).getTime()
            };
        });

        for (let hour = startHour; hour < endHour; hour++) {
            for (let minute = 0; minute < 60; minute += 30) {
                const timeStr = `${hour.toString().padStart(2, '0')}:${minute.toString().padStart(2, '0')}`;
                const currentTime = new Date(`${selectedDate}T${timeStr}:00`).getTime();
                const twoHoursLater = currentTime + (2 * 60 * 60 * 1000);
                
                // Check if this time slot conflicts with any existing booking
                const hasConflict = bookedTimes.some(bookedTime => {
                    // Check if the current time slot overlaps with any booking's 2-hour window
                    const currentSlotOverlaps = (currentTime >= bookedTime.start - (2 * 60 * 60 * 1000) && 
                                               currentTime <= bookedTime.end + (2 * 60 * 60 * 1000));
                    
                    // Check if the current time slot's 2-hour window overlaps with any booking
                    const twoHourWindowOverlaps = (twoHoursLater >= bookedTime.start - (2 * 60 * 60 * 1000) && 
                                                 currentTime <= bookedTime.end + (2 * 60 * 60 * 1000));
                    
                    return currentSlotOverlaps || twoHourWindowOverlaps;
                });

                if (!hasConflict) {
                    const displayTime = new Date(`2000-01-01T${timeStr}:00`).toLocaleTimeString('en-US', {
                        hour: 'numeric',
                        minute: '2-digit'
                    });
                    options.push(`<option value="${timeStr}">${displayTime}</option>`);
                }
            }
        }
        
        return options.join('');
    }

    /**
     * Shows confirmation modal for booking removal
     *
     * @param {Object} info Event data from calendar
     * @returns {void}
     */
    function showRemoveEventModal(info) {
        let modalContent = removeEventModal.querySelector('.modal-content');
        let eventDate = info.event ? new Date(info.event.start) : new Date(info.date);
        let formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        modalContent.innerHTML = `
            <h2>Remove Service</h2>
            <p>Are you sure you want to remove the service on ${formattedDate}?</p>
            <button id="confirmRemove">Yes, Remove</button>
            <button id="cancelRemove">Cancel</button>
        `;

        // Add event listeners to the newly created buttons
        document.getElementById('confirmRemove').addEventListener('click', function() {
            if (info.event) {
                info.event.remove();
            }
            removeEventModal.style.display = "none";
        });

        document.getElementById('cancelRemove').addEventListener('click', function() {
            removeEventModal.style.display = "none";
        });

        removeEventModal.style.display = "block";
    }
});