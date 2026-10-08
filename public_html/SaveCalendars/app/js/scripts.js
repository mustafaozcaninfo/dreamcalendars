
function saveTheme(theme) {
  localStorage.setItem('theme', theme);
}

function loadTheme() {
  return localStorage.getItem('theme') || 'light';
}

$(document).ready(function() {
  let currentTheme = loadTheme();
  if (currentTheme === 'dark') {
    $('body').addClass('dark-mode');
  }

  $('#toggle-theme').click(function() {
    $('body').toggleClass('dark-mode');
    let theme = $('body').hasClass('dark-mode') ? 'dark' : 'light';
    saveTheme(theme);
  });
});

function saveHolidayStatus(showHolidays) {
   localStorage.setItem('showHolidays', JSON.stringify(showHolidays));
}

function loadHolidayStatus() {
   return JSON.parse(localStorage.getItem('showHolidays')) || false;
}

function saveCellContent(dateStr, content) {
   let calendarData = JSON.parse(localStorage.getItem('calendarData')) || {};
   calendarData[dateStr] = content;
   localStorage.setItem('calendarData', JSON.stringify(calendarData));
}

function loadCellContent(dateStr) {
   let calendarData = JSON.parse(localStorage.getItem('calendarData')) || {};
   return calendarData[dateStr] || '';
}

function saveHolidayData(holidayData) {
   localStorage.setItem('holidayData', JSON.stringify(holidayData));
}

function loadHolidayData() {
   return JSON.parse(localStorage.getItem('holidayData')) || {
       "2024-1-1": "New Year's Day",
       "2024-6-14": "Flag Day",
       "2024-7-4": "Independence Day",
       "2024-12-25": "Christmas Day"
       // Daha fazla tatil ekleyin...
   };
}


function saveFirstDayOfWeek(firstDayOfWeek) {
   localStorage.setItem('firstDayOfWeek', firstDayOfWeek);
}

function loadFirstDayOfWeek() {
   return localStorage.getItem('firstDayOfWeek') || 'Sunday';
}

function saveFontSettings(fontType, fontSize) {
   localStorage.setItem('fontType', fontType);
   localStorage.setItem('fontSize', fontSize);
}

function loadFontSettings() {
   return {
       fontType: localStorage.getItem('fontType') || 'Arial',
       fontSize: localStorage.getItem('fontSize') || '12'
   };
}

function saveDayNumberPosition(position) {
   localStorage.setItem('dayNumberPosition', position);
}

function loadDayNumberPosition() {
   return localStorage.getItem('dayNumberPosition') || 'top-right';
}

$(document).ready(function() {
 let today = new Date();
 let year = today.getFullYear();
 let month = today.getMonth(); // Geçerli ay (0-indexed: January = 0, February = 1, ..., December = 11)

  const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

  let holidays = loadHolidayData(); // Yerel depolamadan tatil günlerini yükleyin

  let showHolidays = loadHolidayStatus(); // Tatil günlerinin durumunu yükle
  let firstDayOfWeek = loadFirstDayOfWeek(); // Haftanın ilk gününü yükle
  let fontSettings = loadFontSettings(); // Font ayarlarını yükle
  let dayNumberPosition = loadDayNumberPosition(); // Gün numarası pozisyonunu yükle


  $(document).ready(function() {
    $('#share-calendar').click(function() {
      let calendarData = JSON.parse(localStorage.getItem('calendarData')) || {};
      $.post('share.php', { calendar_data: JSON.stringify(calendarData) }, function(response) {
        alert('Share URL: ' + response);
      });
    });
  });
  
  function updateCalendar() {
    firstDayOfWeek = loadFirstDayOfWeek(); // Haftanın ilk gününü güncelle
 fontSettings = loadFontSettings(); // Font ayarlarını güncelle
 dayNumberPosition = loadDayNumberPosition(); // Gün numarası pozisyonunu güncelle
      const calendarYear = $('#calendar-year');
      const calendarMonth = $('#calendar-month');
      const calendarBody = $('#calendar-body');

      calendarYear.text(year);
      calendarMonth.text(monthNames[month]);

      const daysInMonth = new Date(year, month + 1, 0).getDate();
      const startDay = new Date(year, month, 1).getDay(); // First day of the month (0: Sunday, 1: Monday, ..., 6: Saturday)

      const today = new Date();
      const isCurrentMonth = today.getFullYear() === year && today.getMonth() === month;

      calendarBody.empty();
      let date = 1;

      // İlk gün ayarına göre hafta günlerini düzenle
      const weekdays = firstDayOfWeek === 'Monday' ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] : ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
      $('.weekdays').html(weekdays.map(day => `<div>${day}</div>`).join(''));

      // Start day adjustment for Monday start
      let adjustedStartDay = firstDayOfWeek === 'Monday' ? (startDay === 0 ? 6 : startDay - 1) : startDay;

      for (let i = 0; i < 6; i++) {
          const row = $('<tr></tr>');
          let emptyRow = true;

          for (let j = 0; j < 7; j++) {
              const cell = $('<td></td>').addClass('day-cell');

              if (i === 0 && j < adjustedStartDay) {
                  cell.html('&nbsp;');
                  if (firstDayOfWeek === 'Monday') {
                      if (j === 5) {
                          cell.addClass('sat');
                      } else if (j === 6) {
                          cell.addClass('sun');
                      }
                  } else {
                      if (j === 0) {
                          cell.addClass('sun');
                      } else if (j === 6) {
                          cell.addClass('sat');
                      }
                  }
              } else if (date > daysInMonth) {
                  cell.html('&nbsp;');
                  if (firstDayOfWeek === 'Monday') {
                      if (j === 5) {
                          cell.addClass('sat');
                      } else if (j === 6) {
                          cell.addClass('sun');
                      }
                  } else {
                      if (j === 0) {
                          cell.addClass('sun');
                      } else if (j === 6) {
                          cell.addClass('sat');
                      }
                  }
              } else {
                  let dayClass = '';
                  if (firstDayOfWeek === 'Monday') {
                      if (j === 5) {
                          dayClass = 'sat';
                      } else if (j === 6) {
                          dayClass = 'sun';
                      }
                  } else {
                      if (j === 0) {
                          dayClass = 'sun';
                      } else if (j === 6) {
                          dayClass = 'sat';
                      }
                  }
                  const dateStr = `${year}-${month + 1}-${date}`;
                  const content = loadCellContent(dateStr);  // Hücre içeriğini yükle
                  const holidayText = showHolidays && holidays[dateStr] ? `<span class="holiday">${holidays[dateStr]}</span>` : '';
                  cell.html(`
                      <div class="day-number" style="font-family: ${fontSettings.fontType}; font-size: ${fontSettings.fontSize}px;">${date}</div>
                      <div class="day-content" contenteditable="true">${holidayText} ${content}</div>
                  `);
                  cell.addClass('oDay').addClass(dayClass);
                  if (isCurrentMonth && today.getDate() === date) {
                      cell.addClass('today'); // İçinde bulunduğumuz gün
                  }
                  cell.attr('data-date', dateStr);
                  date++;
                  emptyRow = false;
              }

              row.append(cell);
          }

          if (!emptyRow) {
              calendarBody.append(row);
          }
      }

      // Apply day number position
      applyDayNumberPosition();

      $('.day-cell').click(function() {
          const dayNumber = $(this).find('.day-number').text();
          if (dayNumber === '') return; // Boş hücrelere tıklanmasını engelle

          $('.day-cell').removeClass('selected');
          $(this).addClass('selected');

          const dateStr = $(this).data('date');
          let content = loadCellContent(dateStr);
          let holidayContent = showHolidays && holidays[dateStr] ? holidays[dateStr] : '';

          const textArea = $('<textarea></textarea>');
          textArea.val(content.replace(holidayContent, '').trim()); // Tatil bilgisini çıkar ve kalan içeriği textarea'ya yerleştir
          $(this).html(textArea);

          textArea.focus();
          textArea.on('blur', function() {
              const newContent = $(this).val();
              const cell = $(this).closest('.day-cell');
              saveCellContent(dateStr, newContent);  // Hücre içeriğini kaydet
              cell.html(`
                  <div class="day-number" style="font-family: ${fontSettings.fontType}; font-size: ${fontSettings.fontSize}px;">${dayNumber}</div>
                  <div class="day-content" contenteditable="true">${showHolidays && holidays[dateStr] ? `<span class="holiday">${holidays[dateStr]}</span>` : ''} ${newContent}</div>
              `);
              cell.removeClass('selected');
              if (isCurrentMonth && today.getDate() === parseInt(dayNumber)) {
                  cell.addClass('today'); // İçinde bulunduğumuz günün stilini koru
              }

              // Reapply day number position after content update
              applyDayNumberPosition();
          });
      });
  }

  function applyDayNumberPosition() {
      $('.day-number').css('position', 'absolute');
      switch (dayNumberPosition) {
          case 'top-left':
              $('.day-number').css({ top: '5px', left: '5px', bottom: '', right: '' });
              break;
          case 'top-right':
              $('.day-number').css({ top: '5px', right: '5px', bottom: '', left: '' });
              break;
      }
  }

  $('#toggle-holidays').click(function() {
      showHolidays = !showHolidays;
      saveHolidayStatus(showHolidays); // Tatil günlerinin durumunu kaydet
      $(this).text(showHolidays ? 'Delete Holidays' : 'Add Holidays');
      updateCalendar();
  });

  $('.prev-month-button').click(function() {
      if (month === 0) {
          month = 11;
          year--;
      } else {
          month--;
      }
      updateCalendar();
  });

  $('.next-month-button').click(function() {
      if (month === 11) {
          month = 0;
          year++;
      } else {
          month++;
      }
      updateCalendar();
  });

  $('#clear-calendar').click(function() {
      let calendarData = JSON.parse(localStorage.getItem('calendarData')) || {};

      if (Object.keys(calendarData).length === 0) {
          alert("No calendar data to clear.");
      } else if (confirm("Are you sure you want to clear all calendar data?")) {
          localStorage.removeItem('calendarData');
          updateCalendar();
      }
  });

  updateCalendar(); // Initialize the calendar

// Tatil günlerinin durumuna göre düğme metnini güncelle
$('#toggle-holidays').text(showHolidays ? 'Delete Holidays' : 'Add Holidays');

// Settings popup açma ve kapama işlemleri
$('#settings-button').click(function() {
    $('#settings-popup').removeClass('hidden').show();
});

$('#close-settings').click(function() {
    $('#settings-popup').hide();
});

$('#save-settings').click(function() {
  // Ayarları kaydet ve uygula
  let firstDayOfWeek = $('.first-day-button.active').attr('id') === 'monday-start' ? 'Monday' : 'Sunday';
  let fontType = $('#font-type').val();
  let fontSize = $('#font-size').val();
  if (fontSize > 18) {
      fontSize = 18;
      $('#font-size').val(18); // Input alanındaki değeri güncelle
  }
  let dayNumberPosition = $('#day-number-position').val();

  // CSS Güncellemeleri

  $('table').css('font-family', fontType);
  $('table').css('font-size', fontSize + 'px');
  $('.day-number').css('position', 'absolute');

  switch (dayNumberPosition) {
      case 'top-left':
          $('.day-number').css({ top: '5px', left: '5px', bottom: '', right: '' });
          break;
      case 'top-right':
          $('.day-number').css({ top: '5px', right: '5px', bottom: '', left: '' });
          break;
  }

  // Ayarları yerel depolamada kaydet
  saveFirstDayOfWeek(firstDayOfWeek);
  saveFontSettings(fontType, fontSize);
  saveDayNumberPosition(dayNumberPosition);

  // Ayarlar kaydedildikten sonra takvimi güncelle
  $('#settings-popup').hide();
  updateCalendar();
});


// İlk gün ayarı
$('.first-day-button').click(function() {
    $('.first-day-button').removeClass('active');
    $(this).addClass('active');
    let firstDayOfWeek = $(this).attr('id') === 'monday-start' ? 'Monday' : 'Sunday';
    saveFirstDayOfWeek(firstDayOfWeek);
    updateCalendar();
});

// İlk gün ayarını yerel depolamadan yükle ve uygulama
if (firstDayOfWeek === 'Monday') {
    $('#monday-start').addClass('active');
} else {
    $('#sunday-start').addClass('active');
}

// Aç/Kapat Butonu Olayları
$('#open-popup').click(function() {
    $('#popup-menu').removeClass('hidden').show();
});

$('#close-popup').click(function() {
    $('#popup-menu').hide();
});

// Ay ve Yıl Butonları İçin Olaylar
$('.year-button').click(function() {
    year = parseInt($(this).text());
    updateCalendar();
    $('#popup-menu').hide();
});

$('.month-button').click(function() {
    month = monthNames.indexOf($(this).text());
    updateCalendar();
    $('#popup-menu').hide();
});

// Quick Nav Butonları İçin Olaylar
$('#quick-prev').click(function() {
    if (month === 0) {
        month = 11;
        year--;
    } else {
        month--;
    }
    updateCalendar();
    $('#popup-menu').hide();
});

$('#quick-current').click(function() {
    const today = new Date();
    year = today.getFullYear();
    month = today.getMonth();
    updateCalendar();
    $('#popup-menu').hide();
});

$('#quick-next').click(function() {
    if (month === 11) {
        month = 0;
        year++;
    } else {
        month++;
    }
    updateCalendar();
    $('#popup-menu').hide();
});

// Font ayarlarını yerel depolamadan yükle ve uygula
$('#font-type').val(fontSettings.fontType);
$('#font-size').val(fontSettings.fontSize);
$('#day-number-position').val(dayNumberPosition);
});

function saveFontSettings(fontType, fontSize) {
    localStorage.setItem('fontSettings', JSON.stringify({ fontType, fontSize }));
}

function loadFontSettings() {
    return JSON.parse(localStorage.getItem('fontSettings')) || { fontType: 'Arial', fontSize: 12 };
}

function saveDayNumberPosition(position) {
    localStorage.setItem('dayNumberPosition', position);
}

function loadDayNumberPosition() {
    return localStorage.getItem('dayNumberPosition') || 'top-right';
}
