<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English strings.
 *
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['addpredictionpoint'] = 'Add prediction point';
$string['answer'] = 'Answer';
$string['answered'] = 'Answered';
$string['awardedpoints'] = 'Awarded points';
$string['completiondetail:percent'] = 'Watch at least {$a}% of the video';
$string['completiondetail:predictions'] = 'Make all mandatory predictions';
$string['completionpercent'] = 'Require watched percentage';
$string['completionpredictions'] = 'Require all mandatory predictions';
$string['completionrules'] = 'Completion rules';
$string['continuevideo'] = 'Continue video';
$string['correct'] = 'Correct';
$string['correctanswer'] = 'Correct option value';
$string['correctanswer_help'] = 'Enter the option key, for example A, 1, true or false. Leave blank when there is no objectively correct prediction.';
$string['correctanswers'] = 'Correct answers';
$string['correctness'] = 'Correctness';
$string['errormaxfiles'] = 'Only one file can be uploaded.';
$string['eventpointcreated'] = 'Prediction point created';
$string['eventpointdeleted'] = 'Prediction point deleted';
$string['eventpointupdated'] = 'Prediction point updated';
$string['eventpredictiongraded'] = 'Prediction graded';
$string['eventpredictionsubmitted'] = 'Prediction submitted';
$string['eventreflectionsubmitted'] = 'Reflection submitted';
$string['exportcsv'] = 'Export CSV';
$string['false'] = 'False';
$string['gradeheader'] = 'Grade';
$string['grademode'] = 'Grade calculation';
$string['grademodeblended'] = '50% prediction score + 50% watched percentage';
$string['grademodepredictions'] = 'Prediction score';
$string['grademodeprogress'] = 'Watched percentage';
$string['gradeprediction'] = 'Grade prediction';
$string['gradesaved'] = 'Grade saved.';
$string['hlsunsupported'] = 'This browser cannot play this HLS stream.';
$string['incorrect'] = 'Incorrect';
$string['invalidcorrectanswer'] = 'The correct answer must match one of the option keys.';
$string['invalidgrade'] = 'Grade/points cannot be negative.';
$string['invalidpercent'] = 'Enter a percentage from 0 to 100.';
$string['invalidresponse'] = 'The selected response is not valid for this prediction point.';
$string['invalidtimecode'] = 'Use seconds, MM:SS or HH:MM:SS.';
$string['managepoints'] = 'Manage prediction points';
$string['markcorrectness'] = 'Correctness';
$string['maximumgrade'] = 'Maximum grade';
$string['modulename'] = 'Video Prediction';
$string['modulename_help'] = 'Pauses a video at prediction points so learners must predict what happens next before continuing.';
$string['modulenameplural'] = 'Video Predictions';
$string['noactivities'] = 'There are no Video Prediction activities in this course.';
$string['nopoints'] = 'No prediction points have been created yet.';
$string['notgraded'] = 'Not graded';
$string['optionsrequired'] = 'Add at least one response option.';
$string['pauseonreveal'] = 'Pause again at the reveal time';
$string['pending'] = 'Pending';
$string['pluginadministration'] = 'Video Prediction administration';
$string['pluginname'] = 'Video Prediction';
$string['pointdeleted'] = 'Prediction point deleted.';
$string['pointnotreached'] = 'This prediction point has not been reached yet.';
$string['points'] = 'Points';
$string['pointsaved'] = 'Prediction point saved.';
$string['pointtitle'] = 'Point title';
$string['poster'] = 'Poster image';
$string['posterurl'] = 'Poster URL';
$string['predictionlocked'] = 'This prediction has already been submitted and cannot be changed.';
$string['predictionlockednotice'] = 'Your original prediction is locked after submission and cannot be changed.';
$string['predictionpoint'] = 'Prediction point';
$string['predictionpoints'] = 'Prediction points';
$string['predictionquestion'] = 'Prediction question';
$string['predictionrequired'] = 'Prediction is mandatory before continuing';
$string['predictions'] = 'Predictions';
$string['predictiontimeposition'] = 'Prediction time';
$string['preventseek'] = 'Prevent advancing into unviewed content';
$string['preventseek_help'] = 'Also blocks advancement past any unanswered mandatory prediction point.';
$string['privacy:gradingpath'] = 'Manual grading activity';
$string['privacy:metadata:progress'] = 'Stores consolidated video viewing progress.';
$string['privacy:metadata:progress:completed'] = 'Completion state.';
$string['privacy:metadata:progress:duration'] = 'Known video duration.';
$string['privacy:metadata:progress:lastposition'] = 'Last playback position.';
$string['privacy:metadata:progress:maxwatched'] = 'Highest playback position reached.';
$string['privacy:metadata:progress:percent'] = 'Watched percentage.';
$string['privacy:metadata:progress:segments'] = 'Consolidated watched segments.';
$string['privacy:metadata:progress:timemodified'] = 'Last progress update time.';
$string['privacy:metadata:progress:totalwatchtime'] = 'Total playback time.';
$string['privacy:metadata:progress:uniquewatched'] = 'Unique seconds watched.';
$string['privacy:metadata:progress:userid'] = 'User identifier.';
$string['privacy:metadata:responses'] = 'Stores predictions, grading and post-result reflections.';
$string['privacy:metadata:responses:awarded'] = 'Points awarded.';
$string['privacy:metadata:responses:gradedby'] = 'Identifier of the user who manually graded the prediction.';
$string['privacy:metadata:responses:iscorrect'] = 'Whether the prediction was marked correct.';
$string['privacy:metadata:responses:predictiontime'] = 'Prediction submission time.';
$string['privacy:metadata:responses:reflection'] = 'Post-result reflection.';
$string['privacy:metadata:responses:reflectiontime'] = 'Reflection submission time.';
$string['privacy:metadata:responses:response'] = 'Original immutable prediction response.';
$string['privacy:metadata:responses:responsejson'] = 'Structured response data.';
$string['privacy:metadata:responses:timemodified'] = 'Time when the response or grade was last modified.';
$string['privacy:metadata:responses:understandingchanged'] = 'Whether the learner reported a change in understanding.';
$string['privacy:metadata:responses:userid'] = 'User identifier.';
$string['privacy:progresspath'] = 'Video progress';
$string['privacy:responsespath'] = 'Predictions and reflections';
$string['reflection'] = 'Reflection';
$string['reflectionlocked'] = 'This reflection has already been submitted and cannot be changed.';
$string['reflectionquestion'] = 'Post-result reflection question';
$string['reporttitle'] = 'Video Prediction report';
$string['resetuserdata'] = 'Delete Video Prediction responses, reflections, progress and grades';
$string['responseisrequired'] = 'A response is required.';
$string['responsemultiplechoice'] = 'Multiple choice';
$string['responseopen'] = 'Open response';
$string['responseoptions'] = 'Options';
$string['responseoptions_help'] = 'One option per line. Use key|Label to set an explicit stored value, for example A|The object falls.';
$string['responseoutcomes'] = 'Possible outcomes';
$string['responsetruefalse'] = 'True / false';
$string['responsetype'] = 'Response type';
$string['resultheader'] = 'Result and reflection';
$string['resultnotreached'] = 'The result reveal point has not been reached yet.';
$string['resulttext'] = 'Actual result / explanation';
$string['resumeplayback'] = 'Resume from last position';
$string['revealbeforeprediction'] = 'The reveal time cannot be earlier than the prediction time.';
$string['revealposition'] = 'Result reveal time';
$string['revealposition_help'] = 'Time when the actual outcome becomes available. Leave blank to use prediction time + 10 seconds.';
$string['savereflection'] = 'Save reflection';
$string['seekblocked'] = 'You cannot advance beyond the currently unlocked point.';
$string['sortorder'] = 'Sort order';
$string['sourceupload'] = 'Uploaded video';
$string['sourceurl'] = 'Direct URL / HLS';
$string['sourcevimeo'] = 'Vimeo';
$string['sourceyoutube'] = 'YouTube';
$string['student'] = 'Student';
$string['studentdetails'] = 'Student details';
$string['submitprediction'] = 'Submit prediction';
$string['timeline'] = 'Prediction timeline';
$string['true'] = 'True';
$string['type:multichoice'] = 'Multiple choice';
$string['type:open'] = 'Open response';
$string['type:outcomes'] = 'Possible outcomes';
$string['type:truefalse'] = 'True / false';
$string['understandingchanged'] = 'Understanding changed';
$string['understandingchangedno'] = 'No, my understanding did not change';
$string['understandingchangedyes'] = 'Yes, my understanding changed';
$string['understandingchanges'] = 'Understanding changes';
$string['videofile'] = 'Video file';
$string['videoheader'] = 'Video';
$string['videopredict:addinstance'] = 'Add a new Video Prediction activity';
$string['videopredict:exportreport'] = 'Export Video Prediction reports';
$string['videopredict:grade'] = 'Grade Video Prediction responses';
$string['videopredict:managepoints'] = 'Manage prediction points';
$string['videopredict:view'] = 'View Video Prediction activity';
$string['videopredict:viewreport'] = 'View Video Prediction reports';
$string['videopredictname'] = 'Activity name';
$string['videosource'] = 'Video source';
$string['videourl'] = 'Video URL or ID';
$string['videourl_help'] = 'For YouTube/Vimeo you may paste the full URL or video ID. Direct URLs may point to MP4/WebM or an HLS stream supported by the browser.';
$string['viewdetails'] = 'View details';
$string['watchedpercent'] = 'Watched';
$string['watchedpercentvalue'] = 'Watched: {$a}%';
$string['yourprogress'] = 'Your progress';
