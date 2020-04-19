/**
 * Opening Hours: JS: Backend: Holidays
 */

/** Holidays Meta Box */
jQuery.fn.opHolidays = function() {
  var wrap = jQuery(this);

  var holidaysWrap = wrap.find("tbody");
  var addButton = wrap.find(".add-holiday");

  function init() {
    holidaysWrap.find("tr.op-holiday").each(function(index, element) {
      jQuery(element).opSingleHoliday();
    });
  }

  init();

  function add() {
    var data = {
      action: "op_render_single_dummy_holiday"
    };

    jQuery.post(ajax_object.ajax_url, data, function(response) {
      var newHoliday = jQuery(response).clone();

      newHoliday.opSingleHoliday();

      holidaysWrap.append(newHoliday);
    });
  }

  addButton.click(function(e) {
    e.preventDefault();

    add();
  });
};

/** Holiday Item */
jQuery.fn.opSingleHoliday = function() {
  var wrap = jQuery(this);

  if (wrap.length > 1) {
    wrap.each(function(index, element) {
      jQuery(element).opSingleHoliday();
    });

    return;
  }

  var removeButton = wrap.find(".remove-holiday");
  var inputDateStart = wrap.find("input.date-start");
  var inputDateEnd = wrap.find("input.date-end");

  function remove() {
    wrap.remove();
  }

  removeButton.click(function(e) {
    e.preventDefault();

    remove();
  });

  inputDateStart.datepicker({
    dateFormat: "yy-mm-dd",
    firstDay: openingHoursData.startOfWeek || 0,
    dayNames: openingHoursData.weekdays.full,
    dayNamesMin: openingHoursData.weekdays.short,
    dayNamesShort: openingHoursData.weekdays.short,
    onClose: function(date) {
      inputDateEnd.datepicker("option", "minDate", date);
    }
  });

  inputDateEnd.datepicker({
    dateFormat: "yy-mm-dd",
    firstDay: openingHoursData.startOfWeek || 0,
    dayNames: openingHoursData.weekdays.full,
    dayNamesMin: openingHoursData.weekdays.short,
    dayNamesShort: openingHoursData.weekdays.short,
    onClose: function(date) {
      inputDateStart.datepicker("option", "maxDate", date);
    }
  });

  inputDateStart.focus(function() {
    inputDateStart.blur();
  });

  inputDateEnd.focus(function() {
    inputDateEnd.blur();
  });
};

/**
 * Mapping
 */
jQuery(document).ready(function() {
  jQuery("#op-holidays-wrap").opHolidays();
});
