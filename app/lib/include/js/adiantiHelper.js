
const restKey = 'f73e72b0904de77c10eae5afd90ec737841418416e364e2619c5beb7f735'; // Rest Key

window.adiantiHelper = {
    // URL do projeto
    baseUrl: __adianti_base_url(),

    // dialogs feitos como Promises
    alert(title, message) {
        return new Promise(resolve => __adianti_message(title, message, resolve));
    },
    errorAlert(title, message) {
        return new Promise(resolve => __adianti_error(title, message, resolve));
    },
    confirm(title, message, yes = 'Sim', no = 'Não') {
        return new Promise(resolve => __adianti_question(title, message, () => resolve(true), () => resolve(false), yes, no));
    },
    prompt(message, value) {
        if (value) {
            setTimeout(() => {
                const input = $single('.modal-dialog .bootbox-input');
                input.value = value;
                input.focus();
                input.select();
            }, 50);
        }
        return new Promise(resolve => __adianti_input(message, txt => resolve(txt)));
    },
    toast(message, type, position, faIcon) {
        return new Promise(resolve => {
            type = ['info', 'success', 'warning', 'error', 'question'].includes(type) ? type : 'info';
            position = ['bottomRight', 'bottomLeft', 'topRight', 'topLeft', 'topCenter', 'bottomCenter', 'center'].includes(position) ? position : 'topRight';
            iziToast.settings({onClosing: resolve});
            __adianti_show_toast(type, message, position, faIcon);
        });
    },

    // Carrega um conteúdo sem recarregar a página
    loadPage(obj, func = '', urlParams = {}, delay = 0) {
        const url = adiantiHelper.makeUrl(obj, func, urlParams);
        if (delay) {
            setTimeout(() => __adianti_load_page(url), delay);
        } else {
            __adianti_load_page(url);
        }
    },

    // Carrega um conteúdo no side panel
    loadSidePanel(obj, func = '', data = {}, method = 'POST') {
        adiantiHelper.loadHtml(obj, func, data, method).then(resp => {
            __adianti_load_side_content('adianti_right_panel', resp);
            __adianti_run_after_loads(`?class=${obj}&method=${func}`, resp);
        });
    },

    // Carrega e retorna um HTML específico
    loadHtml(obj, func = '', data = {}, method = 'POST') {
        data = adiantiHelper.makeDataObj(obj, func, data);
        let headers = {};
        if (adiantiHelper.api.restKey) {
            headers.Authorization = `Basic ${adiantiHelper.api.restKey}`
        }
        return ajax(adiantiHelper.baseUrl + '/engine.php', data, method, headers).then(resp => resp.text());
    },

    // Prepara o objecto com os parametros da query na URL
    makeDataObj(obj, func = '', data = {}) {
        let params = { class: obj };
        if (!!func) {
            params.method = func;
        }
        if (JSON.stringify(data) != '{}') {
            params = { ...params, ...data };
        }
        return params;
    },

    // Prepara a query para a URL
    makeUrl(obj, func = '', data = {}) {
        let params = adiantiHelper.makeDataObj(obj, func, data);
        return addUrlParams(params, adiantiHelper.baseUrl + '/index.php');
    },

    // Fecha o painel
    closePanel() {
        Template.closeRightPanel();
    },

    // copiar texto pra área de transferência
    copyText(txt) {
        __adianti_copy_to_clipboard(txt);
    },

    // Debounce
    debounce(func, timeout = 300){
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => func.apply(this, args), timeout);
        };
    },

    // Bloqueia / desbloqueia a tela
    wait(open = true, txt = '') {
        if (open) {
            return __adianti_block_ui(txt);
        }
        return __adianti_unblock_ui();
    },

    // Chamadas para a Rest API
    api: {
        // Valores padrão para o uso da API
        url: __adianti_base_url() + '/rest.php',
        restKey,
        method: 'GET',
        filter: 'json',
        // Executa uma chamada à API
        call(obj, func = '', data = {}, method = adiantiHelper.api.method) {
            data = adiantiHelper.makeDataObj(obj, func, data);
            let headers = {};
            if (adiantiHelper.api.restKey) {
                headers.Authorization = `Basic ${adiantiHelper.api.restKey}`;
            }
            return ajax(adiantiHelper.api.url, data, method, headers).then(response => {
                if (adiantiHelper.api.filter) {
                    return response[adiantiHelper.api.filter]();
                }
                return response;
            });
        }
    },
    
    // Action hooks
    action: {
        // Armazena as funções pelo nome do hook
        actions: {},
        // Adiciona uma função em algum hook
        add(act, fnc) {
            if (adiantiHelper.action.actions[act] === undefined) {
                adiantiHelper.action.actions[act] = [fnc];
            } else {
                adiantiHelper.action.actions[act].push(fnc);
            }
        },
        // Executa as funções listadas em actions[act]
        exec(act, params = null) {
            if (adiantiHelper.action.actions[act] !== undefined) {
                adiantiHelper.action.actions[act].forEach(cb => {
                    if (typeof cb == 'function') {
                        cb.apply(null, params);
                    }
                    if (typeof cb == 'string' && typeof window[cb] == 'function') {
                        window[cb].apply(null, params);
                    }
                });
            }
        },
        // Um modo de disparar nossos eventos a partir de funções de terceiros
        graft(act, fnc) {
            const oldFnc = fnc;
            return function(...params) {
                adiantiHelper.action.exec(act, params);
                return oldFnc(...params);
            };
        }
    }
};

// Actions enxertadas
__adianti_load_page = adiantiHelper.action.graft('loadPage', __adianti_load_page);
__adianti_load_side_content = adiantiHelper.action.graft('openSidePanel', __adianti_load_side_content);
Template.closeRightPanel = adiantiHelper.action.graft('closeSidePanel', Template.closeRightPanel);
