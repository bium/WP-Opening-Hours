<?php
// Customized for BiUM from original file: https://github.com/janizde/WP-Opening-Hours/blob/master/classes/OpeningHours/Module/Shortcode/IsOpen.php
namespace OpeningHours\Module\Shortcode;

use OpeningHours\Entity\IrregularOpening;
use OpeningHours\Entity\Period;
use OpeningHours\Entity\Set;
use OpeningHours\Module\OpeningHours;
use OpeningHours\Util\Dates;
use OpeningHours\Util\Weekdays;

/**
 * Shortcode indicating whether the venue is currently open or not
 *
 * @author      Jannik Portz
 * @package     OpeningHours\Module\Shortcode
 */
class IsOpen extends AbstractShortcode {
  const FILTER_FORMAT_TODAY = 'op_is_open_format_today';
  const FILTER_FORMAT_NEXT = 'op_is_open_format_next';

  /** @inheritdoc */
  protected function init() {
    $this->setShortcodeTag('op-is-open');

    $this->defaultAttributes = array(
      'set_id' => null,
      'open_text' => __('We\'re currently open.', 'wp-opening-hours'),
      'closed_text' => __('We\'re currently closed.', 'wp-opening-hours'),
      'closed_holiday_text' => __('We\'re currently closed for %1$s.', 'wp-opening-hours'),
      'show_next' => false,
      'next_format' => __('We\'re open again on %2$s (%1$s) from %3$s to %4$s', 'wp-opening-hours'),
      'show_today' => 'never',
      'show_closed_holidays' => false,
      'today_format' => __('Opening Hours today: %1$s', 'wp-opening-hours'),
      'before_widget' => '<div class="op-is-open-shortcode">',
      'after_widget' => '</div>',
      'before_title' => '<h3 class="op-is-open-title">',
      'after_title' => '</h3>',
      'title' => null,
      'classes' => null,
      'next_period_classes' => null,
      'open_class' => 'op-open',
      'closed_class' => 'op-closed',
      'date_format' => Dates::getDateFormat(),
      'time_format' => Dates::getTimeFormat(),
      'lang' => 'en',
    );

    $this->validAttributeValues = array(
      'show_next' => array(false, true),
      'show_today' => array('never', 'open', 'always')
    );
  }

  /** @inheritdoc */
  public function shortcode(array $attributes) {
    $setId = $attributes['set_id'];

    $set = OpeningHours::getInstance()->getSet($setId);

    if (!$set instanceof Set) {
      return;
    }

    $is_library_open = $set->isOpen();
    
    $todayData = $set->getDataForDate(Dates::getNow());
    $todayPeriods = $this->getTodaysPeriods($todayData);

    if ($is_library_open) {
        echo '<div class="op-is-open-shortcode opening-hours-status-open"><span class="opening-hours-status">'.($attributes['lang'] == 'fr' ? 'Ouvert' : "Open").'</span><span class="opening-hours-next-status-sep"> &#8231; </span><span class="opening-hours-next-status">'.($attributes['lang'] == 'fr' ? 'jusqu\'à ' : "until ");
        $attr = array(
                    'time_format' => ($attributes['lang'] == 'fr' ? 'G\hi' : "g.ia"),
                    'lang' => $attributes['lang'],
                    );
        echo str_replace('h00', 'h', str_replace('.00', '', $this->formatToday($todayPeriods, $attr)));
        echo '</span> </div>';
    } else {
        echo '<div class="op-is-open-shortcode opening-hours-status-closed"><span class="opening-hours-status">'.($attributes['lang'] == 'fr' ? 'Fermé' : "Closed").'</span><span class="opening-hours-next-status-sep"> &#8231; </span><span class="opening-hours-next-status">';

        $attr = array(
                    'next_format' => 'Ouvre %2$s %1$s à %3$s', 
                    'date_format' => "j M",
                    'time_format' => ($attributes['lang'] == 'fr' ? 'G\hi' : "g.ia"),
                    'lang' => $attributes['lang'],
                    );
        $nextPeriod = $set->getNextOpenPeriod();
        $nextPeriod_string = str_replace('h00', 'h', str_replace('.00', '', $this->formatNext($nextPeriod, $attr)));
        echo $nextPeriod_string;
        echo '</span> </div>';
    }
    

  }

  /**
   * Retrieves holiday names for today
   * @param  array $todayData   Data for today
   * @return string            Extracted holiday name(s)
   */
  public function getTodaysHolidaysCommaSeperated($todayData) {
    if (count($todayData['holidays']) > 0) {
      $holidayNames = array();

      foreach ($todayData['holidays'] as $holiday) {
        array_push($holidayNames, $holiday->getName());
      }

      return implode(', ', $holidayNames);
    }

    return null;
  }

  /**
   * Retrieves periods from today data
   * @param     array   $todayData    Data for today
   * @return    Period[]              Extracted periods
   */
  public function getTodaysPeriods($todayData) {
    if (count($todayData['irregularOpenings']) > 0) {
      /* @var IrregularOpening $io */
      $io = $todayData['irregularOpenings'][0];
      return array($io->createPeriod());
    }

    if (count($todayData['holidays']) > 0) {
      return array();
    }

    return $todayData['periods'];
  }

  /**
   * Formats the todays opening hours message according to shortcode attributes
   * @param     Period[]    $periods    Array of period on that day
   * @param     array       $attributes Shortcode attributes
   * @return    string|null             Formatted today message (after filter 'op_is_open_format_today')
   */
  public function formatToday(array $periods, array $attributes) {
    if (count($periods) < 1) {
      return null;
    }

    $timeFormat = $attributes['time_format'];
    $periodStrings = array_map(function (Period $p) use ($timeFormat) {
      return $p->getFormattedTimeRange($timeFormat);
    }, $periods);

    $periodString = implode(', ', $periodStrings);

    $periodsStart = $periods[0]->getTimeStart()->format($timeFormat);
    $periodsEnd = $periods[count($periods) - 1]->getTimeEnd()->format($timeFormat);
    return $periodsEnd;
    //return sprintf($attributes['today_format'], $periodString, $periodsStart, $periodsEnd);
  }

  /**
   * Formats the next open period message according to shortcode attributes
   * @param     Period    $nextPeriod   The next open period or null if it doesnt exist
   * @param     array     $attributes   Shortcode attributes
   * @return    string|null             Formatted next period message (after filter 'op_is_open_format_next')
   */
  public function formatNext(Period $nextPeriod = null, array $attributes) {
    if (!$nextPeriod instanceof Period) {
      return null;
    }

    // Does it open today?
    if (Dates::compareDate(Dates::getNow(), $nextPeriod->getTimeStart()) == 0) {
        if ($attributes['lang'] == 'fr') {
            return 'Ouvre à ' . $nextPeriod->getTimeStart()->format($attributes['time_format']);
        } else {
            return 'Opens at ' . $nextPeriod->getTimeStart()->format($attributes['time_format']);
        }
    } else {
        // Does it open tomorrow ? 
        if ($nextPeriod->getTimeStart()->format('Ymd') ===  date('Ymd', strtotime('+1 day'))) {
            if ($attributes['lang'] == 'fr') {
                return 'Ouvre à ' . $nextPeriod->getTimeStart()->format($attributes['time_format']) . ' demain';
            } else {
                return 'Opens at ' . $nextPeriod->getTimeStart()->format($attributes['time_format']) . ' tomorrow';
            }
        } else {
            // Opens again another day
            if ($attributes['lang'] == 'fr') {
                return 'Ouvre à ' . $nextPeriod->getTimeStart()->format($attributes['time_format']) . ', ' . Weekdays::getWeekday($nextPeriod->getWeekday())->getShortName() . ' ' . Dates::format('j', $nextPeriod->getTimeStart()) . ' ' . date_i18n("M", (int)$nextPeriod->getTimeStart()->format('U'));
            } else {
                return 'Opens at ' . $nextPeriod->getTimeStart()->format($attributes['time_format']) . ', ' . Weekdays::getWeekday($nextPeriod->getWeekday())->getShortName(). ' ' . Dates::format('j', $nextPeriod->getTimeStart()) . ' ' . date_i18n("M", (int)$nextPeriod->getTimeStart()->format('U'));
            }
        }
    }
    
  }
}
