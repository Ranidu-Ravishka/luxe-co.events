            </div> <!-- End of Main Content Container -->
        </div> <!-- End of Content -->
    </div> <!-- End of Wrapper -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    
    <script>
        $(document).ready(function() {
            // Initialize DataTables if they exist
            if ($.fn.DataTable) {
                if ($('#servicesTable').length && !$.fn.DataTable.isDataTable('#servicesTable')) {
                    $('#servicesTable').DataTable({
                        order: [[0, 'desc']],
                        pageLength: 10
                    });
                }
                if ($('#usersTable').length && !$.fn.DataTable.isDataTable('#usersTable')) {
                    $('#usersTable').DataTable({
                        order: [[0, 'desc']],
                        pageLength: 10
                    });
                }
                if ($('#bookingsTable').length && !$.fn.DataTable.isDataTable('#bookingsTable')) {
                    $('#bookingsTable').DataTable({
                        order: [[0, 'desc']],
                        pageLength: 10
                    });
                }
                if ($('#testimonialsTable').length && !$.fn.DataTable.isDataTable('#testimonialsTable')) {
                    $('#testimonialsTable').DataTable({
                        order: [[4, 'desc']], // Order by date
                        pageLength: 10
                    });
                }
                if ($('#blogPostsTable').length && !$.fn.DataTable.isDataTable('#blogPostsTable')) {
                    $('#blogPostsTable').DataTable({
                        order: [[5, 'desc']], // Order by date
                        pageLength: 10
                    });
                }
            }

            // Sidebar Toggle
            $('#sidebarCollapse').click(function() {
                $('.sidebar').toggleClass('toggled');
                $('#content').toggleClass('full-width');
                
                // Store sidebar state in localStorage
                localStorage.setItem('sidebarToggled', $('.sidebar').hasClass('toggled'));
            });

            // Check and restore sidebar state on page load
            if (localStorage.getItem('sidebarToggled') === 'true') {
                $('.sidebar').addClass('toggled');
                $('#content').addClass('full-width');
            }

            // Handle window resize
            $(window).resize(function() {
                if ($(window).width() <= 768) {
                    $('.sidebar').addClass('toggled');
                    $('#content').addClass('full-width');
                } else {
                    // Restore saved state on larger screens
                    if (localStorage.getItem('sidebarToggled') === 'true') {
                        $('.sidebar').addClass('toggled');
                        $('#content').addClass('full-width');
                    } else {
                        $('.sidebar').removeClass('toggled');
                        $('#content').removeClass('full-width');
                    }
                }
            });

            // Initialize tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Initialize popovers
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        });
    </script>
</body>
</html> 