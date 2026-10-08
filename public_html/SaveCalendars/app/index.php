<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Calendar Application</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body class="flex items-center justify-center min-h-screen bg-gray-100">
  <div class="container container-days bg-white rounded-lg shadow-lg p-4 w-full max-w-4xl">
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center">
      <button class="prev-next-month-button prev-month-button">&lt;</button>
      <button id="open-popup" class="text-2xl font-bold mx-2">
        <span id="calendar-month">June</span> <span id="calendar-year">2024</span>
      </button>
      <button class="prev-next-month-button next-month-button">&gt;</button>
      <button id="clear-calendar" class="prev-next-month-button">Clear</button>
      <button id="settings-button" class="settings-button">Settings</button>
    </div>
  <div class="flex items-center space-x-2">
    <button id="toggle-holidays" class="prev-next-month-button">Add Holidays</button>
    <span>|</span>
    <button id="toggle-theme" class="prev-next-month-button">Dark Mode</button>
    <button id="share-calendar" class="prev-next-month-button bg-blue-500 text-white">Share</button>
  </div>
</div>

<!-- Settings Popup -->
<div id="settings-popup" class="hidden fixed z-50 inset-0 overflow-y-auto">
  <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
    <div class="fixed inset-0 transition-opacity">
      <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>
    <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>
    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
      <div class="popup-content p-6">
        <h2 class="text-xl font-bold mb-4">Settings</h2>

        <div class="mb-4">
          <h3 class="font-semibold">First Day of Week</h3>
          <div class="flex space-x-2">
            <button id="monday-start" class="first-day-button">Monday</button>
            <button id="sunday-start" class="first-day-button">Sunday</button>
          </div>
        </div>

        <div class="mb-4">
          <h3 class="font-semibold">Font Settings</h3>
          <label class="block mb-2">Font Type</label>
          <select id="font-type" class="w-full">
            <option value="Arial">Arial</option>
            <option value="Helvetica">Helvetica</option>
            <option value="Times New Roman">Times New Roman</option>
          </select>
          <label class="block mt-4 mb-2">Font Size</label>
          <input type="number" id="font-size" class="w-full" value="12" max="18">
        </div>

        <div class="mb-4">
          <h3 class="font-semibold">Day Number Position</h3>
          <select id="day-number-position" class="w-full">
            <option value="top-left">Top Left</option>
            <option value="top-right">Top Right</option>
          </select>
        </div>

        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
          <button id="save-settings" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm">Save</button>
          <button id="close-settings" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>

  <!-- Popup Menüsü İçin HTML -->
  <div id="popup-menu">
    <div class="popup-content">
      <h2 class="text-red-600 text-xl mb-4">Select a month</h2>
      <p>Select the month and year you want a calendar for:</p>
      <div class="my-4">
        <h3 class="text-red-600">Year</h3>
        <div class="flex space-x-2">
          <button class="year-button">2022</button>
          <button class="year-button">2023</button>
          <button class="year-button font-bold">2024</button>
          <button class="year-button">2025</button>
          <button class="year-button">2026</button>
        </div>
      </div>
      <div class="my-4">
        <h3 class="text-red-600">Month</h3>
        <div class="flex flex-wrap space-x-2">
          <button class="month-button">January</button>
          <button class="month-button">February</button>
          <button class="month-button">March</button>
          <button class="month-button">April</button>
          <button class="month-button">May</button>
          <button class="month-button font-bold">June</button>
          <button class="month-button">July</button>
          <button class="month-button">August</button>
          <button class="month-button">September</button>
          <button class="month-button">October</button>
          <button class="month-button">November</button>
          <button class="month-button">December</button>
        </div>
      </div>
      <div class="my-4">
        <h3 class="text-red-600">Quick Nav</h3>
        <div class="flex space-x-2">
          <button id="quick-prev" class="quick-nav-button">Previous Month</button>
          <button id="quick-current" class="quick-nav-button">This Month</button>
          <button id="quick-next" class="quick-nav-button">Next Month</button>
        </div>
      </div>
      <button id="close-popup" class="text-blue-600 mt-4">Nevermind, just close this window.</button>
    </div>
  </div>
    <div class="row">
      <div class="col-xs-1 main">
        <div class="weekdays">
          <div>Sun</div>
          <div>Mon</div>
          <div>Tue</div>
          <div>Wed</div>
          <div>Thu</div>
          <div>Fri</div>
          <div>Sat</div>
        </div>
        <table class="calendar">
          <tbody id="calendar-body">
            <!-- JavaScript ile takvim günleri burada oluşturulacak -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <script src="js/scripts.js"></script>
</body>
</html>
