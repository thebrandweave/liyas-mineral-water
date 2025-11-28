// Function to set the active sidebar link based on the current URL
function setActiveSidebarLink() {
    const currentPath = window.location.pathname.split("/").pop();
    const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li');

    allSideMenu.forEach(item => {
        const link = item.querySelector('a');
        if (link && link.getAttribute('href') === currentPath) {
            item.classList.add('active');
        }
    });
}

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	if (window.innerWidth <= 768) {
		// Mobile: toggle sidebar with overlay
		sidebar.classList.toggle('show');
		document.body.classList.toggle('sidebar-open');
	} else {
		// Desktop: toggle hide class
		sidebar.classList.toggle('hide');
	}
});

const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
	if(window.innerWidth < 576) {
		e.preventDefault();
		searchForm.classList.toggle('show');
		if(searchForm.classList.contains('show')) {
			searchButtonIcon.classList.replace('bx-search', 'bx-x');
		} else {
			searchButtonIcon.classList.replace('bx-x', 'bx-search');
		}
	}
});

// Function to handle responsive adjustments on load and resize
function handleResponsive() {
    if (window.innerWidth <= 768) {
        // Mobile: hide sidebar by default, remove hide class
        sidebar.classList.remove('hide');
        sidebar.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    } else {
        // Desktop: show sidebar, remove mobile classes
        sidebar.classList.remove('show');
        document.body.classList.remove('sidebar-open');
        // Keep hide state if it was set
    }

    if (window.innerWidth > 576) {
        if (searchButtonIcon) {
            searchButtonIcon.classList.replace('bx-x', 'bx-search');
        }
        if (searchForm) {
            searchForm.classList.remove('show');
        }
    }
}

// Close sidebar when clicking overlay (mobile only)
document.addEventListener('click', function(e) {
    if (window.innerWidth <= 768 && sidebar && sidebar.classList.contains('show')) {
        // Check if click is outside sidebar
        if (!sidebar.contains(e.target) && !menuBar.contains(e.target)) {
            sidebar.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    }
});

// Initial call on page load
handleResponsive();
window.addEventListener('resize', handleResponsive);

const switchMode = document.getElementById('switch-mode');

// Function to apply the theme based on localStorage
function applyTheme() {
    if (localStorage.getItem('theme') === 'dark') {
        document.body.classList.add('dark');
        switchMode.checked = true;
    } else {
        document.body.classList.remove('dark');
        switchMode.checked = false;
    }
}

// Apply theme on initial load
applyTheme();

switchMode.addEventListener('change', function () {
	if(this.checked) {
		document.body.classList.add('dark');
        localStorage.setItem('theme', 'dark');
	} else {
		document.body.classList.remove('dark');
        localStorage.setItem('theme', 'light');
	}
});

// Set active link on page load
document.addEventListener('DOMContentLoaded', setActiveSidebarLink);