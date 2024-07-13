
function getUrlParams(url = window.location.href) {
    url = new URL(url);
    const params = new URLSearchParams(url.search);
    const vars = {};
    for (const [key, value] of params.entries()) {
        vars[key] = value;
    }
    return vars;
}

function addUrlParams(params, url = window.location.href) {
    let urlObj = new URL(url);
    Object.keys(params).forEach(key => {
        urlObj.searchParams.set(key, params[key]);
    });
    return urlObj.toString();
}

function removeUrlParams(names, url = window.location.href) {
    const urlObj = new URL(url);
    const params = new URLSearchParams(urlObj.search);
    names.forEach(name => params.delete(name));
    return `${urlObj.origin}${urlObj.pathname}?${params.toString()}`;
}

function replaceUrl(url) {
    history.pushState(null, null, url);
}

function tag(tagName = 'div', attrs = {}, content = '') {
    const elem = document.createElement(tagName);
    for (const key in attrs) {
        elem.setAttribute(key, attrs[key]);
    }
    if (content) {
        if (typeof content == 'string') {
            elem.innerHTML = content;
        } else {
            elem.appendChild(content);
        }
    }
    return elem;
}

function addEscBehavior(callback) {
    window.escBehaviors = window.escBehaviors ?? [];
    window.escBehaviors.push(callback);
    if (!window.escBehaviorsSet) {
        document.body.addEventListener('keydown', event => {
            if (event.key == 'Escape') {
                window.escBehaviors.forEach(fn => fn.call(null));
            }
        });
        window.escBehaviorsSet = true;
    }
}

function rootEvent(selector, eventName, callback) {
    document.body.addEventListener(eventName, event => {
        if (event.target.matches(`${selector}, ${selector} *`)) {
            const elem = event.target.closest(selector);
            callback.call(elem, event);
        }
    })
}

function $single(selector, context) {
    if (typeof selector != 'string') {
        return selector;
    }
    return (context ? context : document).querySelector(selector);
}

function $list(selector, context) {
    return (context ? context : document).querySelectorAll(selector);
}

function $apply(selector, fnc, context) {
    const elems = $list(selector, context);
    if (typeof fnc == 'function') {
        Array.from(elems).forEach(el => fnc.call(el, el));
    }
    return elems;
}

function getIndex(element) {
  if (!element || !element.parentNode) {
    return -1;
  }
  const siblings = Array.from(element.parentNode.children);
  return siblings.indexOf(element);
}

function writeScript(src) {
    const script = tag('script', { src, type: 'text/javascript' });
    document.head.appendChild(script);
}

function ajax(url, data = {}, method = 'GET', headers = {}) {
    headers = { 'Content-Type': 'application/x-www-form-urlencoded', ...headers };
    let options = { method, headers };
    if (method.toUpperCase() === 'POST') {
        options.body = new URLSearchParams(data).toString();
    } else {
        url = addUrlParams(data, url);
    }
    return fetch(url, options);
}

function getFormValues(form, filter) {
    form = $single(form);
    const formValues = {};
    if (form) {
        const formData = new FormData(form);
        formData.forEach((value, key) => {
            if (typeof filter == 'function' && !filter(key, value)) {
                return true;
            }
            if (formValues[key]) {
                if (Array.isArray(formValues[key])) {
                    formValues[key].push(value);
                } else {
                    formValues[key] = [formValues[key], value];
                }
            } else {
                formValues[key] = value;
            }
        });
    }
    return formValues;
}

function objFromArray(keys, values) {
    return keys.reduce((obj, key, index) => {
        obj[key] = values[index];
        return obj;
    }, {});
}

function setButtonEnabled(btn, fields) {
    const button = $single(btn);
    if (button) {
        const selectors = fields.split(',');
        let fulfilled = true;
        selectors.forEach(sel => {
            const input = $single(sel);
            if (!input || !input.value) {
                fulfilled = false;
            }
        });
        button.disabled = !fulfilled;
        const fn = fulfilled ? 'remove' : 'add';
        button.classList[fn]('disabled');
    }
}

function showOnOption(combo, value, dependent) {
    if (!dependent.getAttribute('data-display')) {
        dependent.setAttribute('data-display', dependent.style.display);
    }
    const action = event => {
        if (event.target.value == value) {
            dependent.style.display = dependent.getAttribute('data-display');
        } else {
            dependent.style.display = 'none';
        }
    };
    combo.addEventListener('input', action);
    action({target: combo});
}


