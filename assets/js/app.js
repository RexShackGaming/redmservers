document.addEventListener('DOMContentLoaded', function() {

    const toggle = document.querySelector('.mobile-toggle');
    const nav = document.querySelector('.main-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', function() {
            nav.classList.toggle('open');
        });
    }

    document.querySelectorAll('.copy-ip').forEach(function(el) {
        el.addEventListener('click', function() {
            const ip = this.getAttribute('data-ip');
            navigator.clipboard.writeText(ip).then(function() {
                const tooltip = el.querySelector('.copy-tooltip');
                if (tooltip) {
                    tooltip.classList.add('show');
                    setTimeout(function() { tooltip.classList.remove('show'); }, 1500);
                }
            });
        });
    });

    document.querySelectorAll('.vote-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const serverId = this.getAttribute('data-server-id');
            fetch('api/vote.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'server_id=' + serverId
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    const countEl = document.querySelector('.vote-count[data-server-id="' + serverId + '"]');
                    if (countEl) countEl.textContent = data.votes;
                    if (data.user_voted) {
                        btn.classList.add('voted');
                    } else {
                        btn.classList.remove('voted');
                    }
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                }
            })
            .catch(function() {
                // fetch failed silently
            });
        });
    });

    const lightbox = document.querySelector('.lightbox');
    if (lightbox) {
        document.querySelectorAll('.screenshot-thumb').forEach(function(thumb) {
            thumb.addEventListener('click', function() {
                const img = this.querySelector('img');
                if (img) {
                    lightbox.querySelector('img').src = img.src;
                    lightbox.classList.add('active');
                }
            });
        });
        lightbox.addEventListener('click', function() {
            this.classList.remove('active');
        });
    }

    document.querySelectorAll('.flash-auto-dismiss').forEach(function(el) {
        setTimeout(function() {
            el.style.opacity = '0';
            setTimeout(function() { el.remove(); }, 300);
        }, 4000);
    });

    const deleteForms = document.querySelectorAll('.delete-confirm');
    deleteForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this?')) {
                e.preventDefault();
            }
        });
    });

    var statusCard = document.querySelector('.server-status-card');
    if (statusCard) {
        var joinCode = statusCard.getAttribute('data-join-code');
        var serverId = statusCard.getAttribute('data-server-id');

        function setOffline() {
            var loading = document.querySelector('.status-loading');
            var result = document.querySelector('.status-result');
            var error = document.querySelector('.status-error');
            if (loading) loading.style.display = 'none';
            if (result) result.style.display = 'none';
            if (error) error.style.display = 'block';
        }

        function showOnline(data) {
            var loading = document.querySelector('.status-loading');
            var result = document.querySelector('.status-result');
            var error = document.querySelector('.status-error');
            if (!loading) return;
            loading.style.display = 'none';
            result.style.display = 'block';
            error.style.display = 'none';

            var dot = document.getElementById('status-dot');
            if (dot) dot.className = 'status-indicator status-online';
            var st = document.getElementById('status-text');
            if (st) st.textContent = 'Online';

            var sp = document.getElementById('status-players');
            if (sp) sp.textContent = (data.Data.clients || 0) + ' / ' + (data.Data.sv_maxclients || '?');

            var allowlisted = data.Data.vars && data.Data.vars.sv_appearAllowlisted === 'true';
            var wlIcon = document.getElementById('status-allowlisted');
            if (wlIcon) {
                if (allowlisted) {
                    wlIcon.className = 'fas fa-lock';
                    wlIcon.style.color = 'var(--color-danger, #dc3545)';
                } else {
                    wlIcon.className = 'fas fa-lock-open';
                    wlIcon.style.color = 'var(--color-success, #28a745)';
                }
            }

            var sg = document.getElementById('status-gametype');
            if (sg) sg.textContent = data.Data.gametype || '-';
        }

        function checkStatus() {
            var active = true;

            function tryProxy() {
                var ctrl = new AbortController();
                var timer = setTimeout(function() { ctrl.abort(); }, 8000);

                fetch('https://corsproxy.io/?url=' + encodeURIComponent('https://frontend.cfx-services.net/api/servers/single/' + joinCode), {
                    signal: ctrl.signal
                })
                    .then(function(r) {
                        clearTimeout(timer);
                        if (!r.ok) throw new Error('bad status');
                        return r.json();
                    })
                    .then(function(data) {
                        if (active && data && data.Data) {
                            active = false;
                            showOnline(data);
                        } else {
                            tryPhp();
                        }
                    })
                    .catch(function() {
                        clearTimeout(timer);
                        tryPhp();
                    });
            }

            function tryPhp() {
                if (!active) return;
                var ctrl = new AbortController();
                var timer = setTimeout(function() { ctrl.abort(); }, 10000);

                fetch('api/status.php?id=' + serverId, { signal: ctrl.signal })
                    .then(function(r) {
                        clearTimeout(timer);
                        return r.json();
                    })
                    .then(function(data) {
                        if (!active) return;
                        if (data && data.online) {
                            active = false;
                            showOnline({ Data: { clients: data.players, sv_maxclients: data.max_players, gametype: data.gametype, mapname: data.map, vars: { sv_appearAllowlisted: data.allowlisted ? 'true' : 'false' } } });
                        } else {
                            active = false;
                            setOffline();
                        }
                    })
                    .catch(function() {
                        clearTimeout(timer);
                        if (active) { active = false; setOffline(); }
                    });
            }

            tryProxy();
        }

        checkStatus();
        setInterval(checkStatus, 120000);
    }
});
