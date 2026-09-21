# mod_videopredict — Video Prediction

Moodle activity module for prediction-based video learning. The video pauses at teacher-defined moments and asks the
learner to predict what will happen before playback can continue.

## Main features

- Multiple prediction points in one video.
- Open response, multiple choice, true/false and possible-outcome questions.
- Mandatory points block advancement until the original prediction is saved.
- Original predictions are immutable after submission.
- Separate reveal time for the real outcome.
- Optional post-result reflection about whether/why understanding changed.
- HTML5 upload/direct URL, YouTube and Vimeo playback.
- Watched-segment tracking, resume, progress percentage and seek protection.
- Timeline markers for prediction points.
- Automatic scoring for objective questions and manual grading for open responses.
- Gradebook integration using prediction score, watched progress or a 50/50 blend.
- Completion by all mandatory predictions and/or watched percentage.
- Course report, per-student responses, correctness, reflections, changed understanding and CSV export.
- Moodle Privacy API and backup/restore support.

## Requirements

Moodle 4.4 or later (2024042200), PHP version supported by that Moodle release.

## Installation

Install the ZIP through Site administration > Plugins > Install plugins, or extract the `videopredict` directory
into `mod/`.

## License

GNU GPL v3 or later.
