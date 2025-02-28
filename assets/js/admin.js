// Admin JavaScript file
document.addEventListener('DOMContentLoaded', function() {
    // Toggle the side navigation
    const sidebarToggle = document.querySelector('#sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelector('body').classList.toggle('sidebar-toggled');
            document.querySelector('.sidebar').classList.toggle('toggled');
        });
    }

    // Close any open menu accordions when window is resized
    window.addEventListener('resize', function() {
        if (window.innerWidth < 768) {
            document.querySelectorAll('.sidebar .collapse').forEach(function(collapseElement) {
                collapseElement.classList.remove('show');
            });
        }
    });

    // Prevent the content wrapper from scrolling when the fixed side navigation hovered over
    document.addEventListener('scroll', function() {
        const scrollDistance = window.pageYOffset;
        if (scrollDistance > 100) {
            document.querySelector('.scroll-to-top').style.display = 'block';
        } else {
            document.querySelector('.scroll-to-top').style.display = 'none';
        }
    });

    // Scroll to top button
    const scrollToTopButton = document.querySelector('.scroll-to-top');
    if (scrollToTopButton) {
        scrollToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Confirm delete actions
    const deleteButtons = document.querySelectorAll('.btn-delete');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
});

// Admin Dashboard JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded! Some admin features may not work correctly.');
        initVanillaJS();
    } else {
        // jQuery is available, initialize jQuery-dependent features
        initJQuery(jQuery);
    }
    
    // Initialize vanilla JavaScript functionality
    function initVanillaJS() {
        // Toggle the side navigation
        const sidebarToggle = document.querySelector('#sidebarToggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('body').classList.toggle('sidebar-toggled');
                document.querySelector('.sidebar').classList.toggle('toggled');
            });
        }

        // Close any open menu accordions when window is resized
        window.addEventListener('resize', function() {
            if (window.innerWidth < 768) {
                document.querySelectorAll('.sidebar .collapse').forEach(function(collapseElement) {
                    collapseElement.classList.remove('show');
                });
            }
        });

        // Show/hide scroll to top button
        document.addEventListener('scroll', function() {
            const scrollDistance = window.pageYOffset;
            const scrollToTopBtn = document.querySelector('.scroll-to-top');
            if (scrollToTopBtn) {
                if (scrollDistance > 100) {
                    scrollToTopBtn.style.display = 'block';
                } else {
                    scrollToTopBtn.style.display = 'none';
                }
            }
        });

        // Scroll to top button functionality
        const scrollToTopButton = document.querySelector('.scroll-to-top');
        if (scrollToTopButton) {
            scrollToTopButton.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        // Initialize Bootstrap tooltips
        if (typeof bootstrap !== 'undefined') {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Confirm delete actions
        const deleteButtons = document.querySelectorAll('.btn-delete');
        deleteButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        });
    }
    
    // Initialize jQuery functionality
    function initJQuery($) {
        // Toggle the side navigation
        $("#sidebarToggle, #sidebarToggleTop").on('click', function(e) {
            $("body").toggleClass("sidebar-toggled");
            $(".sidebar").toggleClass("toggled");
            if ($(".sidebar").hasClass("toggled")) {
                $('.sidebar .collapse').collapse('hide');
            }
        });

        // Close any open menu accordions when window is resized
        $(window).resize(function() {
            if ($(window).width() < 768) {
                $('.sidebar .collapse').collapse('hide');
            }
        });

        // Scroll to top button appear
        $(document).on('scroll', function() {
            var scrollDistance = $(this).scrollTop();
            if (scrollDistance > 100) {
                $('.scroll-to-top').fadeIn();
            } else {
                $('.scroll-to-top').fadeOut();
            }
        });

        // Smooth scrolling
        $(document).on('click', 'a.scroll-to-top', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, 800);
        });
        
        // Initialize Bootstrap 5 tooltips and popovers
        if (typeof bootstrap !== 'undefined') {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }

        // Close alerts
        $(".alert").on('click', '.btn-close', function() {
            $(this).parent().fadeOut();
        });

        // File input custom display
        $(".custom-file-input").on("change", function() {
            var fileName = $(this).val().split("\\").pop();
            $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
        });

        // Simple rich text editor initialization (if CKEditor is included)
        if (typeof ClassicEditor !== 'undefined') {
            document.querySelectorAll('.rich-text-editor').forEach(function(element) {
                ClassicEditor
                    .create(element)
                    .catch(error => {
                        console.error(error);
                    });
            });
        }

        // Confirm delete actions
        $('.btn-delete').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });

        // Initialize DataTables if available
        if ($.fn.DataTable) {
            $('.datatable').DataTable({
                "responsive": true,
                "language": {
                    "search": "Search:",
                    "paginate": {
                        "previous": "Previous",
                        "next": "Next"
                    }
                }
            });
        }
    }
}); 