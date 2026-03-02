/**
 * Login form handler — hashes password with SHA-256, POSTs to login endpoint.
 *
 * Expects constants on window:
 *   LOGIN_PROCESS_URL  — e.g. "/login/process"
 *   REDIRECT_URL       — e.g. "/"
 */

'use strict';

async function hashString(inputString) {
    var encoder = new TextEncoder();
    var data    = encoder.encode(inputString);
    var buffer  = await crypto.subtle.digest('SHA-256', data);
    var bytes   = Array.from(new Uint8Array(buffer));
    return bytes.map(function(b) { return b.toString(16).padStart(2, '0'); }).join('');
}

function setCookie(name, value) {
    document.cookie = name + '=' + encodeURIComponent(value) + '; path=/';
}

document.addEventListener('DOMContentLoaded', function() {
    var form = document.getElementById('form-login');
    if (!form) return;

    form.addEventListener('submit', function(event) {
        event.preventDefault();

        var user = document.getElementById('user').value;
        var pass = document.getElementById('password').value;

        hashString(pass).then(function(hashedString) {
            var url  = window.LOGIN_PROCESS_URL || '/login/process';
            var data = new FormData();
            data.append('userName', user);
            data.append('password', hashedString);

            fetch(url, { method: 'POST', body: data })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.status === 'Success') {
                        setCookie('sessionreport', data.msg);
                        window.location.href = window.REDIRECT_URL || '/';
                    } else {
                        console.error('Login failed:', data);
                        alert(data.msg || 'Login failed');
                    }
                })
                .catch(function(error) {
                    console.error('Request failed:', error);
                });
        });
    });
});
