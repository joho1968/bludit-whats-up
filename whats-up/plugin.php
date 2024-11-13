<?php
/*
 * What's Up Plugin for Bludit (whats-up)
 *
 * plugin.php (whats-up)
 * Copyright 2024 Joaquim Homrighausen; all rights reserved.
 * Development sponsored by WebbPlatsen i Sverige AB, www.webbplatsen.se
 *
 * This file is part of whats-up. whats-up is free software.
 *
 * whats-up is free software: you may redistribute it and/or modify it
 * under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE v3 as published by
 * the Free Software Foundation.
 *
 * whats-up is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU AFFERO GENERAL PUBLIC LICENSE
 * v3 for more details.
 *
 * You should have received a copy of the GNU AFFERO GENERAL PUBLIC LICENSE v3
 * along with the whats-up package. If not, write to:
 *  The Free Software Foundation, Inc.,
 *  51 Franklin Street, Fifth Floor
 *  Boston, MA  02110-1301, USA.
 */

defined( 'BLUDIT' ) || die( 'That did not work as expected.' );

define( 'WHATSUP_PLUGIN_DEBUG', false );

define( 'WHATSUP_PLUGIN_ENABLED',             'whatsup-plugin-enabled'       );
define( 'WHATSUP_PLUGIN_DISABLED',            'whatsup-plugin-disabled'      );
define( 'WHATSUP_PLUGIN_STATUS',              'whatsup-plugin-status'        );
define( 'WHATSUP_PLUGIN_SIDEBAR_ENABLED',     'whatsup-plugin-sidebar'       );
define( 'WHATSUP_PLUGIN_SIDEBAR_TITLE',       'whatsup-plugin-sidebar-title' );

define( 'WHATSUP_PLUGIN_ICS_URL',             'whatsup-ics-url'              );
define( 'WHATSUP_PLUGIN_ICS_FILE',            'whatsup-ics-file'             );
define( 'WHATSUP_PLUGIN_AGENDA_PAST',         'whatsup-agenda-past'          );
define( 'WHATSUP_PLUGIN_AGENDA_FUTURE',       'whatsup-agenda-future'        );
define( 'WHATSUP_PLUGIN_AGENDA_SHOW_PLACE',   'whatsup-agenda-place'         );
define( 'WHATSUP_PLUGIN_AGENDA_MAX_DAYS',     90                             );
define( 'WHATSUP_PLUGIN_SHOW_WEEKDAY',        'whatsup-plugin-show-weekday'  );
define( 'WHATSUP_PLUGIN_TIME_FORMAT',         'whatsup-plugin-time-format'   );
define( 'WHATSUP_PLUGIN_TIME_FORMAT_24H',     'hhmm'                         );
define( 'WHATSUP_PLUGIN_TIME_FORMAT_AMPM',    'ampm'                         );

define( 'WHATSUP_PLUGIN_MONTH_JAN',           'Jan'                          );
define( 'WHATSUP_PLUGIN_MONTH_FEB',           'Feb'                          );
define( 'WHATSUP_PLUGIN_MONTH_MAR',           'Mar'                          );
define( 'WHATSUP_PLUGIN_MONTH_APR',           'Apr'                          );
define( 'WHATSUP_PLUGIN_MONTH_MAY',           'May'                          );
define( 'WHATSUP_PLUGIN_MONTH_JUN',           'Jun'                          );
define( 'WHATSUP_PLUGIN_MONTH_JUL',           'Jul'                          );
define( 'WHATSUP_PLUGIN_MONTH_AUG',           'Aug'                          );
define( 'WHATSUP_PLUGIN_MONTH_SEP',           'Sep'                          );
define( 'WHATSUP_PLUGIN_MONTH_OCT',           'Oct'                          );
define( 'WHATSUP_PLUGIN_MONTH_NOV',           'Nov'                          );
define( 'WHATSUP_PLUGIN_MONTH_DEC',           'Dec'                          );

define( 'WHATSUP_PLUGIN_DAY_MON',             'Mon'                          );
define( 'WHATSUP_PLUGIN_DAY_TUE',             'Tue'                          );
define( 'WHATSUP_PLUGIN_DAY_WED',             'Wed'                          );
define( 'WHATSUP_PLUGIN_DAY_THU',             'Thu'                          );
define( 'WHATSUP_PLUGIN_DAY_FRI',             'Fri'                          );
define( 'WHATSUP_PLUGIN_DAY_SAT',             'Sat'                          );
define( 'WHATSUP_PLUGIN_DAY_SUN',             'Sun'                          );

use Sabre\VObject;

class WhatsUp extends Plugin {

    protected $whatsup_status_check_disabled = false;
    protected $ics_data = false;
    protected $whatsup_ics_loaded = false;
    protected $whatsup_style_loaded = false;

    protected $agenda_our_timezone = false;
    protected $agenda_right_now = false;
    protected $agenda_right_now_year_string = false;
    protected $agenda_right_now_end = false;
    protected $agenda_time_begin = false;
    protected $agenda_time_end = false;
    protected $agenda_time_begin_string = '';
    protected $agenda_time_begin_short_string = '';
    protected $agenda_time_end_string = '';
    protected $agenda_time_end_short_string = '';
    protected $agenda_our_timezone_for_events = false;
    protected $agenda_events_of_interest = [];
    protected $agenda_our_events = [];

    protected $plugin_status_values = array(
        WHATSUP_PLUGIN_ENABLED,
        WHATSUP_PLUGIN_DISABLED,
    );
    protected $plugin_time_format = array(
        WHATSUP_PLUGIN_TIME_FORMAT_24H,
        WHATSUP_PLUGIN_TIME_FORMAT_AMPM,
    );
    protected $plugin_days_values = array(
        WHATSUP_PLUGIN_DAY_MON,
        WHATSUP_PLUGIN_DAY_TUE,
        WHATSUP_PLUGIN_DAY_WED,
        WHATSUP_PLUGIN_DAY_THU,
        WHATSUP_PLUGIN_DAY_FRI,
        WHATSUP_PLUGIN_DAY_SAT,
        WHATSUP_PLUGIN_DAY_SUN,
    );
    protected $plugin_months_values = array(
        WHATSUP_PLUGIN_MONTH_JAN,
        WHATSUP_PLUGIN_MONTH_FEB,
        WHATSUP_PLUGIN_MONTH_MAR,
        WHATSUP_PLUGIN_MONTH_APR,
        WHATSUP_PLUGIN_MONTH_MAY,
        WHATSUP_PLUGIN_MONTH_JUN,
        WHATSUP_PLUGIN_MONTH_JUL,
        WHATSUP_PLUGIN_MONTH_AUG,
        WHATSUP_PLUGIN_MONTH_SEP,
        WHATSUP_PLUGIN_MONTH_OCT,
        WHATSUP_PLUGIN_MONTH_NOV,
        WHATSUP_PLUGIN_MONTH_DEC,
    );

    public function siteSidebar() {
        global $L;

        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( $this->getPluginStatus() != WHATSUP_PLUGIN_ENABLED ) {
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Plugin is disabled' );
            }
            return( '' );
        }
        if ( ! $this->getAgendaShowSidebar() ) {
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Not enabled for sidebar display' );
            }
            return( '' );
        }
        // Generate content, we will be silent if there are no events
        $this->setupICS();
        if ( ! empty( $this->agenda_our_events ) ) {
            global $L;

            $html  = '<div class="plugin plugin-whats-up">';
            $sidebar_title = $this->getAgendaSidebarTitle();
            if ( ! empty( $sidebar_title ) ) {
                $html .= '<h2 class="plugin-label">' . $sidebar_title . '</h2>';
            }
            $html .= '<div class="plugin-content">';

            $today = $this->agenda_right_now->format( 'Ymd' );
            $html .= $this->agendaStyle();
            $html .= $this->agendaWidgetOpen();
            $use_hhmm_format = ( $this->getTimeFormat() != WHATSUP_PLUGIN_TIME_FORMAT_AMPM );

            foreach( $this->agenda_our_events as $k => $v ) {
                if ( ! empty( $v ) ) {
                    // One or more events exist for this day
                    $html .= $this->agendaDayOpen( ( $k == $today ), $v[0][6], $v[0][7], $v[0][4], $v[0][9] );
                    if ( count( $v ) > 1 ) {
                        uasort( $v, [$this, 'sortTimes' ] );
                    }
                    $html .= $this->agendaDayItemsOpen();
                    foreach( $v as $e ) {
                        if ( $e[1] == '00:00' && $e[2] == '00:00' ) {
                            $time_str = htmlentities( $L->get( 'whatsup-all-day' ) );
                        } elseif ( $use_hhmm_format ) {
                            $time_str = '<time datetime="' . $e[1] . '">' . $e[1] . '</time>-<time datetime="' . $e[2] . '">' . $e[2] . '</time>';
                        } else {
                            $time_str = '<time datetime="' . $e[10] . '">' . $e[10] . '</time>-<time datetime="' . $e[11] . '">' . $e[11] . '</time>';
                        }
                        $html .= $this->agendaDayItem( $time_str, $e[3], $e[8] );
                    }// foreach
                    $html .= $this->agendaDayItemsClose();
                    $html .= $this->agendaDayClose();
                } else {
                    // Day without events
                }
            }// foreach
            $html .= $this->agendaWidgetClose();
            $html .= '</div>';
        } else {
            $html = '';
        }
        return( $html );
    }

    // Check various configuration items to determine if we can process ICS
    protected function checkStatus() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( $this->getPluginStatus() != WHATSUP_PLUGIN_ENABLED ) {
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Plugin is disabled' );
            }
            return( false );
        }
        $our_url = $this->getICSurl();
        $our_file = $this->getICSfile();
        if ( empty( $our_url ) && empty( $our_file ) ) {
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): No .ics source configured' );
            }
            return( false );
        }
        return( true );
    }
    // Fetch ICS data from remote or from a local file
    protected function getICS() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        $ics_source = $this->getICSurl();
        if ( ! empty( $ics_source ) ) {
            $this->ics_data = file_get_contents( $ics_source );
            if ( $this->ics_data === false ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to fetch .ics data from "' . $ics_source . '"' );
                return( false );
            }
            return( true );
        }
        $ics_source = $this->getICSfile();
        if ( ! empty( $ics_source ) ) {
            $ics_source = $this->getICSfileWithPath();
            if ( ! empty( $ics_source ) ) {
                $this->ics_data = file_get_contents( $ics_source );
                if ( $this->ics_data === false ) {
                    if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                        error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to fetch .ics data from "' . $ics_source . '"' );
                    }
                    return( false );
                }
                return( true );
            }
        }
        return( false );
    }
    public function init()  {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        $this->dbFields = array(
            WHATSUP_PLUGIN_STATUS => WHATSUP_PLUGIN_ENABLED,
            WHATSUP_PLUGIN_ICS_URL => '',
            WHATSUP_PLUGIN_ICS_FILE => '',
            WHATSUP_PLUGIN_AGENDA_PAST => 14,
            WHATSUP_PLUGIN_AGENDA_FUTURE => 14,
            WHATSUP_PLUGIN_SHOW_WEEKDAY => true,
            WHATSUP_PLUGIN_TIME_FORMAT => WHATSUP_PLUGIN_TIME_FORMAT_24H,
            WHATSUP_PLUGIN_AGENDA_SHOW_PLACE => true,
            WHATSUP_PLUGIN_SIDEBAR_ENABLED => false,
            WHATSUP_PLUGIN_SIDEBAR_TITLE => '',
        );
    }
    public function post() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( isset( $_POST['save'] ) ) {
            // Do some pre-processing
            if ( ! empty( $_POST[WHATSUP_PLUGIN_AGENDA_PAST] ) ) {
                if ( (int)$_POST[WHATSUP_PLUGIN_AGENDA_PAST] > WHATSUP_PLUGIN_AGENDA_MAX_DAYS ) {
                    $_POST[WHATSUP_PLUGIN_AGENDA_PAST] = WHATSUP_PLUGIN_AGENDA_MAX_DAYS;
                } elseif ( (int)$_POST[WHATSUP_PLUGIN_AGENDA_PAST] <= 0 ) {
                    $_POST[WHATSUP_PLUGIN_AGENDA_PAST] = 0;
                }
            }
            if ( ! empty( $_POST[WHATSUP_PLUGIN_AGENDA_FUTURE] ) ) {
                if ( (int)$_POST[WHATSUP_PLUGIN_AGENDA_FUTURE] > WHATSUP_PLUGIN_AGENDA_MAX_DAYS ) {
                    $_POST[WHATSUP_PLUGIN_AGENDA_FUTURE] = WHATSUP_PLUGIN_AGENDA_MAX_DAYS;
                } elseif ( (int)$_POST[WHATSUP_PLUGIN_AGENDA_FUTURE] <= 0 ) {
                    $_POST[WHATSUP_PLUGIN_AGENDA_FUTURE] = 0;
                }
            }
            if ( ! empty( $_POST[WHATSUP_PLUGIN_SHOW_WEEKDAY] ) && $_POST[WHATSUP_PLUGIN_SHOW_WEEKDAY] == WHATSUP_PLUGIN_SHOW_WEEKDAY ) {
                $_POST[WHATSUP_PLUGIN_SHOW_WEEKDAY] = true;
            } else {
                $_POST[WHATSUP_PLUGIN_SHOW_WEEKDAY] = false;
            }
            if ( ! empty( $_POST[WHATSUP_PLUGIN_AGENDA_SHOW_PLACE] ) && $_POST[WHATSUP_PLUGIN_AGENDA_SHOW_PLACE] == WHATSUP_PLUGIN_AGENDA_SHOW_PLACE ) {
                $_POST[WHATSUP_PLUGIN_AGENDA_SHOW_PLACE] = true;
            } else {
                $_POST[WHATSUP_PLUGIN_AGENDA_SHOW_PLACE] = false;
            }
            if ( ! empty( $_POST[WHATSUP_PLUGIN_SIDEBAR_ENABLED] ) && $_POST[WHATSUP_PLUGIN_SIDEBAR_ENABLED] == WHATSUP_PLUGIN_SIDEBAR_ENABLED ) {
                $_POST[WHATSUP_PLUGIN_SIDEBAR_ENABLED] = true;
            } else {
                $_POST[WHATSUP_PLUGIN_SIDEBAR_ENABLED] = false;
            }
            if ( empty( $_POST[WHATSUP_PLUGIN_TIME_FORMAT] ) ) {
                $_POST[WHATSUP_PLUGIN_TIME_FORMAT] = WHATSUP_PLUGIN_TIME_FORMAT_24H;
            } elseif ( $_POST[WHATSUP_PLUGIN_TIME_FORMAT] != WHATSUP_PLUGIN_TIME_FORMAT_AMPM ) {
                $_POST[WHATSUP_PLUGIN_TIME_FORMAT] = WHATSUP_PLUGIN_TIME_FORMAT_24H;
            }
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): ' . print_r( $_POST, true ) );
            }
        }
        parent::post();
    }

    // Inspirational credit/kudos: WordPress Core
    // Function to generate the regex pattern for parsing [shortcode_tag] shortcodes, similar to WordPress' shortcode regex
    private function getCustomTagRegEx( $the_tag ) {
        return(
            '\\['                              // Opening bracket
            . '(\\[?)'                         // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($the_tag)"                    // 2: Shortcode name (e.g., "mytag")
            . '(\\b[^\\]]*?)'                  // 3: Attributes (if any), non-greedy
            . '(?:(\\/)|'                      // 4: Self-closing tag ...
            . '\\](.*?)'                       // 5: ...or closing bracket and content inside
            . '\\[\\/\\2\\])?'                 // Closing shortcode (optional for self-closing tags)
            . '(\\]?)' );                      // 6: Optional second closing bracket for escaping shortcodes: [[tag]]
    }
    // Process [shortcode_tag] shortcodes similar to WordPress' shortcode handling structure
    private function processCustomTags( $content, $tag_regex ) {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Processing content' );
        }
        $callback = function( $matches ) use ( $tag_regex ) {
            $tag = $matches[2];
            if ( $tag == 'whatsup' ) {
                // Process our tag
                if ( ! empty( $matches[5] ) ) {
                    $no_events_text = $matches[5];
                } else {
                    $no_events_text = '';
                }

                if ( ! empty( $this->agenda_our_events ) ) {
                    global $L;

                    $today = $this->agenda_right_now->format( 'Ymd' );
                    $html .= $this->agendaStyle();
                    $html .= $this->agendaWidgetOpen();
                    $use_hhmm_format = ( $this->getTimeFormat() != WHATSUP_PLUGIN_TIME_FORMAT_AMPM );

                    foreach( $this->agenda_our_events as $k => $v ) {
                        if ( ! empty( $v ) ) {
                            // One or more events exist for this day
                            $html .= $this->agendaDayOpen( ( $k == $today ), $v[0][6], $v[0][7], $v[0][4], $v[0][9] );
                            if ( count( $v ) > 1 ) {
                                uasort( $v, [$this, 'sortTimes' ] );
                            }
                            $html .= $this->agendaDayItemsOpen();
                            foreach( $v as $e ) {
                                if ( $e[1] == '00:00' && $e[2] == '00:00' ) {
                                    $time_str = htmlentities( $L->get( 'whatsup-all-day' ) );
                                } elseif ( $use_hhmm_format ) {
                                    $time_str = '<time datetime="' . $e[1] . '">' . $e[1] . '</time>-<time datetime="' . $e[2] . '">' . $e[2] . '</time>';
                                } else {
                                    $time_str = '<time datetime="' . $e[10] . '">' . $e[10] . '</time>-<time datetime="' . $e[11] . '">' . $e[11] . '</time>';
                                }
                                $html .= $this->agendaDayItem( $time_str, $e[3], $e[8] );
                            }// foreach
                            $html .= $this->agendaDayItemsClose();
                            $html .= $this->agendaDayClose();
                        } else {
                            // Day without events
                        }
                    }// foreach
                    $html .= $this->agendaWidgetClose();
                } else {
                    $html = $no_events_text;
                }
                return( $html );
            }
            $content = isset( $matches[5] ) ? $matches[5] : null;
            if ( $matches[3] === '/' ) {
                return( $processed_content );
            } else {
                $processed_content = $this->processCustomTags( $content, $tag_regex );
                return( $processed_content );
            }
        };
        return( preg_replace_callback( "/$tag_regex/s", $callback, $content ) );
    }
    // Make sure we skip content insdide <pre>..</pre>
    private function preProcessCustomTags( $content ) {
        $shortcode_whatsup = $this->getCustomTagRegEx( 'whatsup' );
        // Split the content by <pre> tags, we will skip the content inside <pre> tags
        $parts = preg_split( '/(<pre.*?>.*?<\/pre>)/is', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
        foreach( $parts as &$part ) {
            // If the part is not inside a <pre> tag, process the shortcodes
            if ( ! preg_match('/^<pre.*?>.*?<\/pre>$/is', $part ) ) {
                $part = $this->processCustomTags( $part, $shortcode_whatsup );
            }
        }
        return( implode('', $parts) );
    }
    // Sort event times within a day to something reasonable
    protected function sortTimes( $a, $b ) {
        if ( $a[1] == $b[1] ) {
            if ( $a[2] < $b[2] ) {
                return( -1 );
            } elseif( $a[2] > $b[2] ) {
                return( 1 );
            }
            return( 0 );
        }
        if ( $a[1] < $b[1] ) {
            return( -1 );
        }
        return( 1 );
    }
    // Setup ICS data
    protected function setupICS() {
        $this->getICSurl();
        $this->getICSfile();
        if ( $this->whatsup_ics_loaded ) {
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): ICS already loaded, skipping' );
            }
            return( true );
        }
        if ( $this->checkStatus() ) {
            if ( $this->getICS() ) {
                include dirname(__FILE__) . '/externals/vendor/autoload.php';
                // Setup baseline
                $this->agenda_our_timezone = date_default_timezone_get();
                $this->agenda_right_now = new DateTimeImmutable( 'now', new DateTimeZone( $this->agenda_our_timezone ) );
                $this->agenda_right_now = new DateTimeImmutable( $this->agenda_right_now->format( 'Ymd' ) . '00:00:00', new DateTimeZone( $this->agenda_our_timezone ) );
                $this->agenda_right_now_end = new DateTimeImmutable( $this->agenda_right_now->format( 'Ymd' ) . '23:59:59', new DateTimeZone( $this->agenda_our_timezone ) );
                $this->agenda_right_now_year_string = $this->agenda_right_now->format( 'Y' );
                // Convert data to ICS
                try {
                    $ics = VObject\Reader::read( $this->ics_data, VObject\Reader::OPTION_FORGIVING );
                } catch( \Throwable $e ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception "' . $e->getMessage() . '"' );
                    $this->whatsup_status_check_disabled = true;
                    return( false );
                }
                // Setup for parsing
                try {
                    $this->agenda_time_begin = $this->agenda_right_now->sub( new DateInterval( 'P' . (int)$this->getPastAgendaDays() . 'D' ) );
                    $this->agenda_time_end = $this->agenda_right_now->add( new DateInterval( 'P' . (int)$this->getFutureAgendaDays() . 'D' ) );
                } catch( \Throwable $e ) {
                    error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Exception "' . $e->getMessage() . '"' );
                    $this->whatsup_status_check_disabled = true;
                    return( false );
                }
                $this->agenda_time_begin_string = $this->agenda_time_begin->format( 'Y-m-d' );
                $this->agenda_time_begin_short_string = $this->agenda_time_begin->format( 'Ymd' );
                $this->agenda_time_end_string = $this->agenda_time_end->format( 'Y-m-d' );
                $this->agenda_time_end_short_string = $this->agenda_time_end->format( 'Ymd' );
                VObject\Settings::$minDate = $this->agenda_time_begin_string;
                VObject\Settings::$maxDate = $this->agenda_time_end_string;
                VObject\Settings::$maxRecurrences = 1000000;
                $this->agenda_our_timezone_for_events = new DateTimeZone( $this->agenda_our_timezone );
                // Using expand() will remove RECUR information
                $ics = $ics->expand( new DateTime( $this->agenda_time_begin_string . ' 00:00:00' ), new DateTime( $this->agenda_time_end_string . ' 23:59:59' ) );
                // Process events
                $this->agenda_events_of_interest = [];
                foreach($ics->VEVENT as & $vevent) {
                    $time_start = $vevent->dtstart->getDateTime( $this->agenda_our_timezone_for_events );
                    $dstart = $time_start->format( 'Ymd' );
                    if ( $vevent->dtstart->hasTime() ) {
                        $tstart = $dstart . ' ' . $time_start->format( 'His' );
                    } else {
                        $tstart = $dstart;
                    }
                    $time_end = $vevent->dtend->getDateTime( $this->agenda_our_timezone_for_events );
                    $dend = $time_end->format( 'Ymd' );
                    if ( $vevent->dtend->hasTime() ) {
                        $tend = $dend . ' ' . $time_end->format( 'His' );
                    } else {
                        $tend = $dend;
                    }
                    if ( ! $vevent->isInTimeRange( $this->agenda_right_now, $this->agenda_right_now_end ) ) {
                        if ( $dend < $this->agenda_time_begin_short_string || $dstart > $this->agenda_time_end_short_string ) {
                            // Outside of our window of interest
                            continue;
                        }
                    }
                    $this->agenda_events_of_interest[] = $vevent;
                }// foreach
                // Prepare our "days" array as array keys
                $events = new DatePeriod( $this->agenda_time_begin, new DateInterval( 'P1D' ), 60 );
                if ( method_exists( $events, 'getIterator' ) ) {
                    // PHP 8.x+
                    $event_iterator = $events->getIterator();
                } else {
                    // PHP <8.x
                    $event_iterator = $events;
                }
                $this->agenda_our_events = [];
                foreach( $event_iterator as $event ) {
                    $this->agenda_our_events[ $event->format( 'Ymd' )] = array();
                }
                // Check all events that match a day in our range
                foreach( $this->agenda_events_of_interest as & $vevent ) {
                    $time_start = $vevent->dtstart->getDateTime( $this->agenda_our_timezone_for_events );
                    $dstart = $time_start->format( 'Ymd' );
                    if ( ! isset( $this->agenda_our_events[$dstart] ) ) {
                        // We don't want this
                        continue;
                    }
                    if ( $vevent->dtstart->hasTime() ) {
                        $tstart = $dstart . ' ' . $time_start->format( 'His' );
                    } else {
                        $tstart = $dstart;
                    }
                    $time_end = $vevent->dtend->getDateTime( $this->agenda_our_timezone_for_events );
                    $dend = $time_end->format( 'Ymd' );
                    if ( $vevent->dtend->hasTime() ) {
                        $tend = $dend . ' ' . $time_end->format( 'His' );
                    } else {
                        $tend = $dend;
                    }
                    $event_uid = (string)$vevent->uid;
                    $event_text = (string)$vevent->summary;
                    $display_start = $time_start->format( 'H:i' );
                    $display_start_ampm = $time_start->format( 'g:ia' );
                    $display_day = strtolower( $time_start->format( 'D' ) );
                    $display_day_num = $time_start->format( 'd' );
                    $display_month_name = strtolower( $time_start->format( 'M' ) );
                    $display_end = $time_end->format( 'H:i' );
                    $display_end_ampm = $time_end->format( 'g:ia' );
                    $display_date = $time_start->format( 'Y-m-d' );
                    $display_location = (string)$vevent->location;
                    if ( $this->agenda_right_now_year_string != $time_start->format( 'Y' ) ) {
                        $display_year = $time_start->format( 'Y' );
                    } else {
                        $display_year = '';
                    }

                    if ( $vevent->isInTimeRange( $this->agenda_right_now, $this->agenda_right_now_end ) ) {
                        $this->agenda_our_events[ $dstart ][] = array(
                            $event_uid,
                            $display_start,
                            $display_end,
                            $event_text,
                            $display_day,
                            $display_date,
                            $display_day_num,
                            $display_month_name,
                            $display_location,
                            $display_year,
                            $display_start_ampm,
                            $display_end_ampm );
                    } elseif ( $vevent->isInTimeRange( $this->agenda_time_begin, $this->agenda_time_end ) ) {
                        $this->agenda_our_events[ $dstart ][] = array(
                            $event_uid,
                            $display_start,
                            $display_end,
                            $event_text,
                            $display_day,
                            $display_date,
                            $display_day_num,
                            $display_month_name,
                            $display_location,
                            $display_year,
                            $display_start_ampm,
                            $display_end_ampm );
                    }
                }// foreach

                $this->whatsup_ics_loaded = true;
                return( true );
            }
        } else {
            $this->whatsup_status_check_disabled = true;
        }
        return( false );
    }
    /*
    public function beforeAdminLoad() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    public function afterAdminLoad() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        $this->setupICS();
    }
    /*
    public function beforeSiteLoad() {
        global $staticContent;
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function afterSiteLoad() {
        global $staticContent;
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function beforeAll() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function siteBodyBegin() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    /*
    public function siteBodyEnd() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
    }
    */
    public function siteHead() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        return( $this->agendaStyle() );
    }
    protected function processContent() {
        $text = ob_get_clean();
        if ( $text !== false ) {
            $text = $this->preProcessCustomTags( $text );
            echo $text;
        }
    }
    public function pageBegin() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( $this->getPluginStatus() === WHATSUP_PLUGIN_ENABLED ) {
            ob_start();
        }
    }
    public function pageEnd() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( $this->getPluginStatus() === WHATSUP_PLUGIN_ENABLED ) {
            $this->setupICS();
            $this->processContent();
        }
    }
    // Functions to output agenda content
    protected function agendaStyle() {
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . ')' );
        }
        if ( $this->whatsup_style_loaded ) {
            if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
                error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): CSS already setup, skipping' );
            }
            return( '' );
        }
        $css_vars_file = dirname( __FILE__ ) . '/css/' . 'whatsup_vars.css';
        $css_vars = file_get_contents( $css_vars_file );
        if ( $css_vars === false ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to load CSS vars ("' . $css_vars_file . '")' );
        }
        $css_file = dirname( __FILE__ ) . '/css/' . 'whatsup.css';
        $css = file_get_contents( $css_file );
        if ( $css === false ) {
            error_log( basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ ) . '(' . __FUNCTION__ . '): Unable to load CSS vars ("' . $css_file . '")' );
        }
        $this->whatsup_style_loaded = true;
        $html = '';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        $html .= '<style>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        $html .= $css_vars;
        $html .= $css;
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        $html .= '</style>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaWidgetOpen() {
        $html = '<div class="whatsup-widget">';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaWidgetClose() {
        $html = '</div>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaDayOpen( $today, $day_num, $month_name, $day_name, $year_number = '' ) {
        global $L;

        $html = '<div class="whatsup-day">';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        $html .= '<div class="whatsup-day-num';
        if ( $today ) {
            $html .= ' whatsup-day-num-today';
        }
        $html .= '">';
        if ( $this->getShowWeekday() && ! empty( $day_name ) ) {
            $html .= '<span class="whatsup-day-num-weekday">' . htmlentities( $L->get( 'whatsup-day-'. $day_name ) ) . '</span><br/>';
        }
        $html .= htmlentities( $day_num ) . '<br/>' . htmlentities( $L->get( 'whatsup-month-' . $month_name ) );
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        if ( ! empty( $year_number ) ) {
            $html .= '<div class="whatsup-day-num-year">' . htmlentities( $year_number ) . '</div>';
        }
        $html .= '</div>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaDayClose() {
        $html = '</div>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaDayItemsOpen() {
        $html = '<div class="whatsup-day-all-items">';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaDayItemsClose() {
        $html = '</div>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    protected function agendaDayItem( $event_time, $event_info, $event_location = '' ) {
        $html = '<div class="whatsup-day-item">';
        $html .= '<div class="whatsup-day-item-time">' . $event_time . '</div>';
        $html .= '<div class="whatsup-day-item-info">' . htmlentities( $event_info );
        if ( ! empty( $event_location ) ) {
            $html .= '<div class="whatsup-day-item-location" title="' . htmlentities( $event_location ) . '">' . htmlentities( $event_location ) . '</div>';
        }
        $html .= '</div>';
        $html .= '</div>';
        if ( defined( 'WHATSUP_PLUGIN_DEBUG' ) && WHATSUP_PLUGIN_DEBUG ) {
            $html .= "\n";
        }
        return( $html );
    }
    // Form
    public function form() {
        global $L;
        global $site;

        $html = '';

        // Plugin status
        $current_setting = $this->getPluginStatus();
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_STATUS . '">';
        $html .= htmlentities( $L->get( WHATSUP_PLUGIN_STATUS ) );
        if ( $current_setting == WHATSUP_PLUGIN_DISABLED ) {
            $html .= ' (<span class="text-danger">' . htmlentities( $L->get( WHATSUP_PLUGIN_DISABLED ) ) . '</span>)';
        }
        $html .= '</label>';
        $html .= '<select class="form-select" id="' . WHATSUP_PLUGIN_STATUS . '" name="' . WHATSUP_PLUGIN_STATUS . '" aria-describedby="' . WHATSUP_PLUGIN_STATUS . 'Help">';
        if ( ! in_array( $current_setting, $this->plugin_status_values ) ) {
            $current = WHATSUP_PLUGIN_ENABLED;
        }
        foreach( $this->plugin_status_values as $k ) {
            $html .= '<option value="' . $k . '"';
            if ( $current_setting == $k ) {
                $html .= ' selected';
            }
            $html .= '>' . htmlentities( $L->get( $k ) ) . '</option>';
        }
        $html .= '</select>';
        $html .= '<div id="' . WHATSUP_PLUGIN_STATUS . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_STATUS ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // .ics URL
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_ICS_URL . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_ICS_URL ) ) . '</label>';
        $html .= '<input id="' . WHATSUP_PLUGIN_ICS_URL . '" name="' . WHATSUP_PLUGIN_ICS_URL . '" type="text" class="form-control" aria-describedby="' . WHATSUP_PLUGIN_ICS_URL . 'Help" value="' . $this->getICSurl() . '" placeholder="https://" />';
        $html .= '<div id="' . WHATSUP_PLUGIN_ICS_URL . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_ICS_URL ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // .ics file (local)
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_ICS_FILE . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_ICS_FILE ) ) . '</label>';
        $html .= '<input id="' . WHATSUP_PLUGIN_ICS_FILE . '" name="' . WHATSUP_PLUGIN_ICS_FILE . '" type="text" class="form-control" aria-describedby="' . WHATSUP_PLUGIN_ICS_URL . 'Help" value="' . $this->getICSfile() . '" placeholder="somefile.ics" />';
        $our_file = $this->getICSfileWithPath();
        if ( ! empty( $our_file ) ) {
            clearstatcache( true, $our_file );
            if ( ! is_readable( $our_file ) || ! is_file( $our_file ) ) {
                $html .= '<div id="' . WHATSUP_PLUGIN_ICS_FILE . 'Error" class="form-text text-danger">';
                $html .= '<span class="text-monospace small">' . $our_file . '</span><br/>' .
                         htmlentities( $L->get( 'noexist-' . WHATSUP_PLUGIN_ICS_FILE ) ) . '</div>';
            }
        }
        $html .= '<div id="' . WHATSUP_PLUGIN_ICS_FILE . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_ICS_FILE ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Look this may days back and foward
        $html .= '<div class="row">';
        $html .= '<div class="col-6 col-lg-5 col-xl-4 mb-3">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_AGENDA_PAST . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_AGENDA_PAST ) ) . '</label>';
        $html .= '<input maxlength="3" size="3" style="max-width:100px;" id="' . WHATSUP_PLUGIN_AGENDA_PAST . '" name="' . WHATSUP_PLUGIN_AGENDA_PAST . '" type="text" class="form-control" aria-describedby="' . WHATSUP_PLUGIN_AGENDA_PAST . 'Help" value="' . $this->getPastAgendaDays() . '" placeholder="0-' . WHATSUP_PLUGIN_AGENDA_MAX_DAYS . '" />';
        $html .= '<div id="' . WHATSUP_PLUGIN_AGENDA_PAST . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_AGENDA_PAST ) ) . '</div>';
        $html .= '</div>';
        $html .= '<div class="col-6 col-lg-5 col-xl-4 mb-3">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_AGENDA_FUTURE . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_AGENDA_FUTURE ) ) . '</label>';
        $html .= '<input maxlength="3" size="3" style="max-width:100px;" id="' . WHATSUP_PLUGIN_AGENDA_FUTURE . '" name="' . WHATSUP_PLUGIN_AGENDA_FUTURE . '" type="text" class="form-control" aria-describedby="' . WHATSUP_PLUGIN_AGENDA_PAST . 'Help" value="' . $this->getFutureAgendaDays() . '" placeholder="0-' . WHATSUP_PLUGIN_AGENDA_MAX_DAYS . '" />';
        $html .= '<div id="' . WHATSUP_PLUGIN_AGENDA_FUTURE . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_AGENDA_FUTURE ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Show weekday above date and time format
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $html .= '<div class="form-check">';
        $html .= '<input class="form-check-input" type="checkbox" value="' . WHATSUP_PLUGIN_SHOW_WEEKDAY . '" aria-label="' .
                     htmlentities( $L->get( WHATSUP_PLUGIN_SHOW_WEEKDAY ) ) . '" aria-describedby="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . 'Help" name="' . WHATSUP_PLUGIN_SHOW_WEEKDAY . '" id="' . WHATSUP_PLUGIN_SHOW_WEEKDAY . '"';
        if ( $this->getShowWeekday() ) {
            $html .= ' checked';
        }
        $html .= ' >';
        $html .= '<label class="form-check-label h6 pt-1" for="' . WHATSUP_PLUGIN_SHOW_WEEKDAY . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_SHOW_WEEKDAY ) ) . '</label>';
        $html .= '</div>';
        $html .= '<div id="' . WHATSUP_PLUGIN_SHOW_WEEKDAY . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_SHOW_WEEKDAY ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Time format
        $html .= '<div class="row">';
        $html .= '<div class="col-7 col-lg-5 col-xl-4 mb-3">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_TIME_FORMAT . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_TIME_FORMAT ) ) . '</label>';
        $current_format = $this->getTimeFormat();
        $html .= '<select class="form-select" id="' . WHATSUP_PLUGIN_TIME_FORMAT . '" name="' . WHATSUP_PLUGIN_TIME_FORMAT . '" aria-describedby="' . WHATSUP_PLUGIN_TIME_FORMAT . 'Help">';
        foreach( $this->plugin_time_format as $k ) {
            $html .= '<option value="' . $k . '"';
            if ( $current_format == $k ) {
                $html .= ' selected';
            }
            $html .= '>' . htmlentities( $L->get( $k ) ) . '</option>';
        }
        $html .= '</select>';
        $html .= '<div id="' . WHATSUP_PLUGIN_TIME_FORMAT . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_TIME_FORMAT ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Show location of event
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $html .= '<div class="form-check">';
        $html .= '<input class="form-check-input" type="checkbox" value="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . '" aria-label="' .
                     htmlentities( $L->get( WHATSUP_PLUGIN_AGENDA_SHOW_PLACE ) ) . '" aria-describedby="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . 'Help" name="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . '" id="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . '"';
        if ( $this->getAgendaShowPlace() ) {
            $html .= ' checked';
        }
        $html .= ' >';
        $html .= '<label class="form-check-label h6 pt-1" for="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_AGENDA_SHOW_PLACE ) ) . '</label>';
        $html .= '</div>';
        $html .= '<div id="' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_AGENDA_SHOW_PLACE ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Show in sidebar hook
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8">';
        $html .= '<div class="form-check">';
        $html .= '<input class="form-check-input" type="checkbox" value="' . WHATSUP_PLUGIN_SIDEBAR_ENABLED . '" aria-label="' .
                     htmlentities( $L->get( WHATSUP_PLUGIN_SIDEBAR_ENABLED ) ) . '" aria-describedby="' . WHATSUP_PLUGIN_SIDEBAR_ENABLED . 'Help" name="' . WHATSUP_PLUGIN_SIDEBAR_ENABLED . '" id="' . WHATSUP_PLUGIN_SIDEBAR_ENABLED . '"';
        if ( $this->getAgendaShowSidebar() ) {
            $html .= ' checked';
        }
        $html .= ' >';
        $html .= '<label class="form-check-label h6 pt-1" for="' . WHATSUP_PLUGIN_SIDEBAR_ENABLED . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_SIDEBAR_ENABLED ) ) . '</label>';
        $html .= '</div>';
        $html .= '<div id="' . WHATSUP_PLUGIN_SIDEBAR_ENABLED . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_SIDEBAR_ENABLED ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Sidebar title
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 mb-3">';
        $html .= '<label class="form-label h6 mb-1" for="' . WHATSUP_PLUGIN_SIDEBAR_TITLE . '">' . htmlentities( $L->get( WHATSUP_PLUGIN_SIDEBAR_TITLE ) ) . '</label>';
        $html .= '<input id="' . WHATSUP_PLUGIN_SIDEBAR_TITLE . '" name="' . WHATSUP_PLUGIN_SIDEBAR_TITLE . '" type="text" class="form-control" aria-describedby="' . WHATSUP_PLUGIN_SIDEBAR_TITLE . 'Help" value="' . $this->getAgendaSidebarTitle() . '" />';
        $html .= '<div id="' . WHATSUP_PLUGIN_SIDEBAR_TITLE . 'Help" class="form-text text-muted">' . htmlentities( $L->get( 'help-' . WHATSUP_PLUGIN_SIDEBAR_TITLE ) ) . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        // Usage
        $html .= '<div class="row">';
        $html .= '<div class="col-12 col-lg-10 col-xl-8 p-3 mt-3 alert alert-primary" role="alert" style="max-width: 65% !important; margin-left: 20px !important;">';
        $html .= '<p class="h3">' . htmlentities( $L->get( 'whatsup-usage-header' ) ) . '</p>';
        $html .= '<p>' . htmlentities( $L->get( 'whatsup-usage-help' ) ) . '</p>';
        $html .= '</div>';
        $html .= '</div>';

        $this->setupICS();

        if ( $this->whatsup_status_check_disabled || ! is_object( $this->agenda_right_now ) ) {
            $html .= '<div class="row">';
            $html .= '<div class="col-12 col-lg-10 col-xl-8 p-3 mt-3 alert alert-danger" role="alert" style="max-width: 65% !important; margin-left: 20px !important;">';
            $html .= '<p class="h3">' . htmlentities( $L->get( 'whatsup-sample-header' ) ) . '</p>';
            $html .= '<p>' . htmlentities( $L->get( 'whatsup-sample-no-sample' ) ) . '</p>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            $html .= '<div class="row">';
            $html .= '<div class="col-12 col-lg-10 col-xl-8 p-3 mt-3" style="max-width: 65% !important; margin-left: 20px !important;">';
            $html .= '<p class="h3">' . htmlentities( $L->get( 'whatsup-sample-header' ) ) . '</p>';
            $html .= '<p class="small">' . htmlentities( $L->get( 'whatsup-baseline-for-events-is' ) ) . ' ' . $this->agenda_right_now->format( 'Y-m-d H:i:s' ) . ' - ' . $this->agenda_right_now_end->format( 'Y-m-d H:i:s' ) . '. ';
            $html .= htmlentities( $L->get( 'whatsup-start-of-window-is' ) ) . ': ' . $this->agenda_time_begin->format( 'Y-m-d' );
            $html .= ', ' . htmlentities( $L->get( 'whatsup-end-of-window-is' ) ) . ': ' . $this->agenda_time_end->format( 'Y-m-d' ) . '</p>';

            $today = $this->agenda_right_now->format( 'Ymd' );

            $html .= $this->agendaStyle();
            $html .= $this->agendaWidgetOpen();

            $use_hhmm_format = ( $this->getTimeFormat() != WHATSUP_PLUGIN_TIME_FORMAT_AMPM );
            foreach( $this->agenda_our_events as $k => $v ) {
                if ( ! empty( $v ) ) {
                    // One or more events exist for this day
                    $html .= $this->agendaDayOpen( ( $k == $today ), $v[0][6], $v[0][7], $v[0][4], $v[0][9] );
                    if ( count( $v ) > 1 ) {
                        uasort( $v, [$this, 'sortTimes' ] );
                    }
                    $html .= $this->agendaDayItemsOpen();
                    foreach( $v as $e ) {
                        if ( $e[1] == '00:00' && $e[2] == '00:00' ) {
                            $time_str = htmlentities( $L->get( 'whatsup-all-day' ) );
                        } elseif ( $use_hhmm_format ) {
                            $time_str = '<time datetime="' . $e[1] . '">' . $e[1] . '</time>-<time datetime="' . $e[2] . '">' . $e[2] . '</time>';
                        } else {
                            $time_str = '<time datetime="' . $e[10] . '">' . $e[10] . '</time>-<time datetime="' . $e[11] . '">' . $e[11] . '</time>';
                        }
                        $html .= $this->agendaDayItem( $time_str, $e[3], $e[8] );
                    }// foreach
                    $html .= $this->agendaDayItemsClose();
                    $html .= $this->agendaDayClose();
                } else {
                    // Day without events
                }
            }// foreach
            $html .= $this->agendaWidgetClose();

            $html .= '</div>';
            $html .= '</div>';
        }

        return $html;
    }

    public function getPluginStatus() {
        return( $this->getValue( WHATSUP_PLUGIN_STATUS ) );
    }
    public function getICSurl() {
        return( $this->getValue( WHATSUP_PLUGIN_ICS_URL ) );
    }
    public function getICSfile() {
        return( $this->getValue( WHATSUP_PLUGIN_ICS_FILE ) );
    }
    public function getICSfileWithPath() {
        $the_ics_file = basename( $this->getICSfile() );
        if ( ! empty( $the_ics_file ) ) {
            return( dirname( __FILE__ ) . '/ics/' . $the_ics_file );
        }
        return( '' );
    }
    public function getPastAgendaDays() {
        return( $this->getValue( WHATSUP_PLUGIN_AGENDA_PAST ) );

    }
    public function getFutureAgendaDays() {
        return( $this->getValue( WHATSUP_PLUGIN_AGENDA_FUTURE ) );

    }
    public function getShowWeekday() {
        return( $this->getValue( WHATSUP_PLUGIN_SHOW_WEEKDAY ) );
    }
    public function getTimeFormat() {
        $ts = $this->getValue( WHATSUP_PLUGIN_TIME_FORMAT );
        if ( $ts != WHATSUP_PLUGIN_TIME_FORMAT_AMPM ) {
            $ts = WHATSUP_PLUGIN_TIME_FORMAT_24H;
        }
        return( $ts );
    }
    public function getAgendaShowPlace() {
        return( $this->getValue( WHATSUP_PLUGIN_AGENDA_SHOW_PLACE ) );
    }
    public function getAgendaShowSidebar() {
        return( $this->getValue( WHATSUP_PLUGIN_SIDEBAR_ENABLED ) );
    }
    public function getAgendaSidebarTitle() {
        return( $this->getValue( WHATSUP_PLUGIN_SIDEBAR_TITLE ) );
    }

}
