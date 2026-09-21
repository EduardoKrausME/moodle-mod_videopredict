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
 * Video Prediction player.
 *
 * @module mod_videopredict/player
 * @package   mod_videopredict
 * @copyright 2026 Eduardo Kraus
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax', 'core/notification'], function (Ajax, Notification) {
    const qs = (root, selector) => root.querySelector(selector);
    const qsa = (root, selector) => Array.from(root.querySelectorAll(selector));

    const loadScript = (url, globalName) => new Promise((resolve, reject) => {
        if (globalName && window[globalName]) {
            resolve(window[globalName]);
            return;
        }
        const existing = document.querySelector('script[data-videopredict-src="' + url + '"]');
        if (existing) {
            existing.addEventListener('load', () => resolve(globalName ? window[globalName] : true));
            existing.addEventListener('error', reject);
            return;
        }
        const script = document.createElement('script');
        script.src = url;
        script.async = true;
        script.dataset.videopredictSrc = url;
        script.onload = () => resolve(globalName ? window[globalName] : true);
        script.onerror = reject;
        document.head.appendChild(script);
    });

    class Html5Adapter {
        constructor(video, url, poster) {
            this.video = video;
            video.hidden = false;
            video.src = url;
            if (poster) {
                video.poster = poster;
            }
        }

        ready() {
            return new Promise(resolve => this.video.readyState > 0 ? resolve() : this.video.addEventListener('loadedmetadata', resolve, {once: true}));
        }

        onTime(callback) {
            this.video.addEventListener('timeupdate', callback);
        }

        onPlay(callback) {
            this.video.addEventListener('play', callback);
        }

        onPause(callback) {
            this.video.addEventListener('pause', callback);
        }

        onSeeking(callback) {
            this.video.addEventListener('seeking', callback);
        }

        time() {
            return this.video.currentTime || 0;
        }

        duration() {
            return Number.isFinite(this.video.duration) ? this.video.duration : 0;
        }

        seek(value) {
            this.video.currentTime = Math.max(0, value);
        }

        play() {
            return this.video.play();
        }

        pause() {
            this.video.pause();
        }
    }

    class YoutubeAdapter {
        constructor(container, url) {
            this.container = container;
            this.url = url;
            this.callbacks = {time: [], play: [], pause: [], seeking: []};
            container.hidden = false;
        }

        extractId() {
            const match = this.url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([A-Za-z0-9_-]{6,})/);
            return match ? match[1] : this.url.trim();
        }

        ready() {
            return new Promise((resolve, reject) => {
                const start = () => {
                    try {
                        this.player = new window.YT.Player(this.container, {
                            videoId: this.extractId(),
                            playerVars: {rel: 0},
                            events: {
                                onReady: () => {
                                    this.timer = setInterval(() => this.callbacks.time.forEach(cb => cb()), 500);
                                    resolve();
                                },
                                onStateChange: event => {
                                    if (event.data === window.YT.PlayerState.PLAYING) this.callbacks.play.forEach(cb => cb());
                                    if (event.data === window.YT.PlayerState.PAUSED) this.callbacks.pause.forEach(cb => cb());
                                }
                            }
                        });
                    } catch (e) {
                        reject(e);
                    }
                };
                if (window.YT && window.YT.Player) {
                    start();
                    return;
                }
                const previous = window.onYouTubeIframeAPIReady;
                window.onYouTubeIframeAPIReady = () => {
                    if (typeof previous === 'function') previous();
                    start();
                };
                loadScript('https://www.youtube.com/iframe_api').catch(reject);
            });
        }

        onTime(cb) {
            this.callbacks.time.push(cb);
        }

        onPlay(cb) {
            this.callbacks.play.push(cb);
        }

        onPause(cb) {
            this.callbacks.pause.push(cb);
        }

        onSeeking(cb) {
            this.callbacks.seeking.push(cb);
        }

        time() {
            return this.player ? this.player.getCurrentTime() || 0 : 0;
        }

        duration() {
            return this.player ? this.player.getDuration() || 0 : 0;
        }

        seek(v) {
            if (this.player) {
                this.player.seekTo(Math.max(0, v), true);
                this.callbacks.seeking.forEach(cb => cb());
            }
        }

        play() {
            if (this.player) this.player.playVideo();
        }

        pause() {
            if (this.player) this.player.pauseVideo();
        }
    }

    class VimeoAdapter {
        constructor(container, url) {
            this.container = container;
            this.url = url;
            this.current = 0;
            this.length = 0;
            this.callbacks = {time: [], play: [], pause: [], seeking: []};
            container.hidden = false;
        }

        async ready() {
            await loadScript('https://player.vimeo.com/api/player.js', 'Vimeo');
            const opts = /^\d+$/.test(this.url.trim()) ? {id: Number(this.url.trim())} : {url: this.url};
            this.player = new window.Vimeo.Player(this.container, opts);
            await this.player.ready();
            this.length = await this.player.getDuration();
            this.player.on('timeupdate', event => {
                this.current = event.seconds;
                this.length = event.duration || this.length;
                this.callbacks.time.forEach(cb => cb());
            });
            this.player.on('play', () => this.callbacks.play.forEach(cb => cb()));
            this.player.on('pause', () => this.callbacks.pause.forEach(cb => cb()));
            this.player.on('seeked', event => {
                this.current = event.seconds;
                this.callbacks.seeking.forEach(cb => cb());
            });
        }

        onTime(cb) {
            this.callbacks.time.push(cb);
        }

        onPlay(cb) {
            this.callbacks.play.push(cb);
        }

        onPause(cb) {
            this.callbacks.pause.push(cb);
        }

        onSeeking(cb) {
            this.callbacks.seeking.push(cb);
        }

        time() {
            return this.current || 0;
        }

        duration() {
            return this.length || 0;
        }

        seek(v) {
            if (this.player) this.player.setCurrentTime(Math.max(0, v));
        }

        play() {
            if (this.player) return this.player.play();
        }

        pause() {
            if (this.player) return this.player.pause();
        }
    }

    class Controller {
        constructor(root, config) {
            this.root = root;
            this.config = config;
            this.maxPosition = Number(config.maxposition || 0);
            this.lastTick = null;
            this.segmentStart = null;
            this.lastFlush = Date.now();
            this.playing = false;
            this.points = [];
            this.activePoint = null;
            this.modal = qs(root, '[data-region="prediction-modal"]');
        }

        async start() {
            this.adapter = this.createAdapter();
            await this.adapter.ready();
            this.duration = this.adapter.duration();
            await this.refreshState();
            this.positionMarkers();
            if (this.config.resumeplayback && Number(this.config.lastposition) > 1) {
                this.adapter.seek(Math.min(Number(this.config.lastposition), this.maxPosition));
            }
            this.bind();
        }

        createAdapter() {
            const source = this.config.source;
            if (source === 'youtube') return new YoutubeAdapter(qs(this.root, '[data-region="iframe-player"]'), this.config.url);
            if (source === 'vimeo') return new VimeoAdapter(qs(this.root, '[data-region="iframe-player"]'), this.config.url);
            return new Html5Adapter(qs(this.root, '[data-region="html5-player"]'), this.config.url, this.root.dataset.poster || '');
        }

        bind() {
            this.adapter.onPlay(() => {
                this.playing = true;
                this.lastTick = Date.now();
                this.segmentStart = this.adapter.time();
            });
            this.adapter.onPause(() => {
                this.playing = false;
                this.flush();
            });
            this.adapter.onSeeking(() => this.guardSeek());
            this.adapter.onTime(() => this.tick());
            qsa(this.root, '[data-point-id]').forEach(marker => marker.addEventListener('click', () => {
                const point = this.points.find(p => p.id === Number(marker.dataset.pointId));
                if (point && (point.answered || this.adapter.time() + 0.5 >= Number(point.timeposition))) this.openPoint(point, point.answered);
            }));
            qs(this.modal, '[data-region="modal-close"]').addEventListener('click', () => this.closeModal());
            qs(this.modal, '[data-region="continue-button"]').addEventListener('click', () => {
                this.closeModal(true);
                this.adapter.play();
            });
            qs(this.modal, '[data-region="submit-prediction"]').addEventListener('click', () => this.submitPrediction());
            qs(this.modal, '[data-region="submit-reflection"]').addEventListener('click', () => this.submitReflection());
            window.addEventListener('beforeunload', () => this.flush());
        }

        async refreshState() {
            const result = await Ajax.call([{
                methodname: 'mod_videopredict_get_state',
                args: {cmid: this.config.cmid}
            }])[0];
            this.points = result.points.map(p => Object.assign({}, p, {choices: JSON.parse(p.choicesjson || '[]')}));
            this.maxPosition = Number(result.progress.maxposition || this.maxPosition);
            qs(this.root, '[data-region="progress-percent"]').textContent = Number(result.progress.percent || 0).toFixed(1) + '%';
        }

        tick() {
            const now = Date.now();
            const time = this.adapter.time();
            if (this.playing) {
                if (this.lastTick === null) this.lastTick = now;
                if (this.segmentStart === null) this.segmentStart = time;
                if (time > this.maxPosition + 0.75) {
                    this.adapter.pause();
                    this.adapter.seek(Math.max(0, this.maxPosition - 0.25));
                    Notification.addNotification({message: this.config.strings.seekblocked, type: 'warning'});
                    return;
                }
                const mandatory = this.points.find(p => p.required && !p.answered && time >= Number(p.timeposition) - 0.25);
                if (mandatory) {
                    this.adapter.pause();
                    this.openPoint(mandatory, false);
                    return;
                }
                const optional = this.points.find(p => !p.required && !p.answered && !p.prompted && time >= Number(p.timeposition) - 0.25);
                if (optional) {
                    optional.prompted = true;
                    this.adapter.pause();
                    this.openPoint(optional, false);
                    return;
                }
                const reveal = this.points.find(p => p.answered && !p.reflected && p.pauseonreveal && !p.revealprompted && time >= Number(p.revealposition) - 0.25);
                if (reveal) {
                    reveal.revealprompted = true;
                    this.adapter.pause();
                    this.flush().then(() => this.refreshState()).then(() => {
                        const fresh = this.points.find(p => p.id === reveal.id);
                        if (fresh) this.openPoint(fresh, true);
                    });
                    return;
                }
                if (now - this.lastFlush > 5000) this.flush();
            }
        }

        guardSeek() {
            const time = this.adapter.time();
            if (time > this.maxPosition + 0.75) {
                this.adapter.seek(Math.max(0, this.maxPosition - 0.25));
                Notification.addNotification({message: this.config.strings.seekblocked, type: 'warning'});
            }
        }

        async flush() {
            if (this.segmentStart === null) return;
            const end = Math.min(this.adapter.time(), this.maxPosition);
            const now = Date.now();
            const elapsed = this.lastTick ? Math.max(0, (now - this.lastTick) / 1000) : Math.max(0, end - this.segmentStart);
            const start = Math.min(this.segmentStart, end);
            this.segmentStart = end;
            this.lastTick = now;
            this.lastFlush = now;
            try {
                const result = await Ajax.call([{
                    methodname: 'mod_videopredict_update_progress',
                    args: {
                        cmid: this.config.cmid,
                        start,
                        end,
                        position: end,
                        duration: this.adapter.duration(),
                        elapsed
                    }
                }])[0];
                this.maxPosition = Number(result.maxposition || this.maxPosition);
                qs(this.root, '[data-region="progress-percent"]').textContent = Number(result.percent || 0).toFixed(1) + '%';
                await this.refreshState();
            } catch (e) {
                Notification.exception(e);
            }
        }

        positionMarkers() {
            const duration = this.adapter.duration();
            if (!duration) return;
            qsa(this.root, '[data-point-id]').forEach(marker => {
                const pct = Math.max(0, Math.min(100, (Number(marker.dataset.time) / duration) * 100));
                marker.style.setProperty('--point-left', pct + '%');
                marker.dataset.positionReady = '1';
            });
        }

        showModal() {
            this.modal.style.display = 'block';
            this.modal.classList.add('show');
            this.modal.removeAttribute('aria-hidden');
            document.body.classList.add('modal-open');
        }

        closeModal(force) {
            if (this.activePoint && this.activePoint.required && !this.activePoint.answered && !force) return;
            this.modal.style.display = 'none';
            this.modal.classList.remove('show');
            this.modal.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('modal-open');
            this.activePoint = null;
        }

        openPoint(point, showResult) {
            this.activePoint = point;
            qs(this.modal, '[data-region="modal-error"]').hidden = true;
            qs(this.modal, '[data-region="modal-title"]').textContent = point.title;
            const close = qs(this.modal, '[data-region="modal-close"]');
            close.style.visibility = point.required && !point.answered ? 'hidden' : 'visible';
            const predictionStep = qs(this.modal, '[data-region="prediction-step"]');
            const resultStep = qs(this.modal, '[data-region="result-step"]');
            const submit = qs(this.modal, '[data-region="submit-prediction"]');
            const continueButton = qs(this.modal, '[data-region="continue-button"]');
            const submitReflection = qs(this.modal, '[data-region="submit-reflection"]');
            continueButton.textContent = this.config.strings.continue;
            submit.textContent = this.config.strings.submit;
            submitReflection.textContent = this.config.strings.reflection;
            qs(this.modal, '[data-region="changed-yes"]').textContent = this.config.strings.changedyes;
            qs(this.modal, '[data-region="changed-no"]').textContent = this.config.strings.changedno;
            if (!point.answered && !showResult) {
                predictionStep.hidden = false;
                resultStep.hidden = true;
                submit.hidden = false;
                submitReflection.hidden = true;
                continueButton.hidden = point.required;
                qs(this.modal, '[data-region="prediction-question"]').innerHTML = point.question;
                qs(this.modal, '[data-region="locked-notice"]').textContent = this.config.strings.predictionlocked;
                this.renderInput(point);
            } else {
                predictionStep.hidden = true;
                resultStep.hidden = false;
                submit.hidden = true;
                continueButton.hidden = false;
                qs(this.modal, '[data-region="original-answer"]').textContent = point.response || '';
                qs(this.modal, '[data-region="correctness"]').textContent = point.iscorrect < 0 ? '' : (point.iscorrect ? this.config.strings.correct : this.config.strings.incorrect);
                qs(this.modal, '[data-region="result-text"]').innerHTML = point.revealed ? point.resulttext : '';
                const reflectionArea = qs(this.modal, '[data-region="reflection-area"]');
                if (point.revealed && point.reflectionquestion && !point.reflected) {
                    reflectionArea.hidden = false;
                    submitReflection.hidden = false;
                    qs(this.modal, '[data-region="reflection-question"]').innerHTML = point.reflectionquestion;
                } else {
                    reflectionArea.hidden = true;
                    submitReflection.hidden = true;
                }
            }
            this.showModal();
        }

        renderInput(point) {
            const box = qs(this.modal, '[data-region="prediction-input"]');
            box.replaceChildren();
            if (point.responsetype === 'open') {
                const textarea = document.createElement('textarea');
                textarea.className = 'form-control';
                textarea.rows = 4;
                textarea.dataset.responseInput = '1';
                box.appendChild(textarea);
                return;
            }
            point.choices.forEach(choice => {
                const wrap = document.createElement('div');
                wrap.className = 'form-check mb-2';
                const input = document.createElement('input');
                input.className = 'form-check-input';
                input.type = 'radio';
                input.name = 'videopredict-response';
                input.value = choice.value;
                input.id = 'vp-' + point.id + '-' + choice.value.replace(/[^a-z0-9_-]/gi, '-');
                input.dataset.responseInput = '1';
                const label = document.createElement('label');
                label.className = 'form-check-label';
                label.htmlFor = input.id;
                label.textContent = choice.label;
                wrap.append(input, label);
                box.appendChild(wrap);
            });
        }

        async submitPrediction() {
            const point = this.activePoint;
            if (!point) return;
            let response = '';
            const textarea = qs(this.modal, 'textarea[data-response-input]');
            if (textarea) response = textarea.value.trim();
            else {
                const checked = qs(this.modal, 'input[data-response-input]:checked');
                if (checked) response = checked.value;
            }
            if (!response) {
                this.showError('A response is required.');
                return;
            }
            try {
                const result = await Ajax.call([{
                    methodname: 'mod_videopredict_submit_prediction',
                    args: {cmid: this.config.cmid, pointid: point.id, response}
                }])[0];
                this.maxPosition = Number(result.maxposition || this.maxPosition);
                await this.refreshState();
                const marker = qs(this.root, '[data-point-id="' + point.id + '"]');
                if (marker) marker.classList.add('is-answered');
                const summary = qs(this.root, '[data-point-summary="' + point.id + '"] [data-region="point-status"]');
                if (summary) {
                    summary.classList.remove('bg-secondary');
                    summary.classList.add('bg-success');
                    summary.textContent = '✓';
                }
                const fresh = this.points.find(p => p.id === point.id);
                this.activePoint = fresh || point;
                this.closeModal(true);
                this.adapter.play();
            } catch (e) {
                this.showError(e.message || String(e));
            }
        }

        async submitReflection() {
            const point = this.activePoint;
            if (!point) return;
            const reflection = qs(this.modal, '[data-region="reflection-text"]').value.trim();
            const changed = qs(this.modal, 'input[name="understandingchanged"]:checked');
            try {
                await Ajax.call([{
                    methodname: 'mod_videopredict_submit_reflection',
                    args: {
                        cmid: this.config.cmid,
                        pointid: point.id,
                        reflection,
                        changed: changed ? Number(changed.value) : -1
                    }
                }])[0];
                await this.refreshState();
                this.closeModal(true);
                this.adapter.play();
            } catch (e) {
                this.showError(e.message || String(e));
            }
        }

        showError(message) {
            const error = qs(this.modal, '[data-region="modal-error"]');
            error.textContent = message;
            error.hidden = false;
        }
    }

    const init = config => {
        const root = document.querySelector('.mod-videopredict[data-cmid="' + config.cmid + '"]');
        if (!root) return;
        new Controller(root, config).start().catch(Notification.exception);
    };
    return {init};
});
