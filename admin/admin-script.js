// Function to set the active sidebar link based on the current URL
function setActiveSidebarLink() {
    const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li');
    const currentPath = window.location.pathname.split("/").pop();

    allSideMenu.forEach(item => {
        const link = item.querySelector('a');
        if (link.getAttribute('href') === currentPath) {
            item.classList.add('active');
        } else {
            item.classList.remove('active');
        }
    });
}


// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
	sidebar.classList.toggle('hide');
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

if(window.innerWidth < 768) {
	sidebar.classList.add('hide');
} else if(window.innerWidth > 576) {
	searchButtonIcon.classList.replace('bx-x', 'bx-search');
	searchForm.classList.remove('show');
}

window.addEventListener('resize', function () {
	if(this.innerWidth > 576) {
		searchButtonIcon.classList.replace('bx-x', 'bx-search');
		searchForm.classList.remove('show');
	}
});

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
