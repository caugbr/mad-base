
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {

        // Config - mostra / esconde parametros por tipo
        rootEvent('input[name="normal-items"],input[name="admin-items"]', 'click', event => {
            const fn = event.target.checked ? 'add' : 'remove';
            event.target.form.classList[fn](event.target.name);
        });

        // Config - alterna entre habilitado e desabilitado para editar um parametro do sistema
        rootEvent('.edit-admin-var', 'click', event => {
            const button = event.target.closest('.edit-admin-var');
            const name = button.getAttribute('data-name');
            const elem = $single(`[name="${name}"]`);
            const group = elem.closest('.form-group');
            if (group.matches('.edit-admin-vars')) {
                group.classList.remove('edit-admin-vars');
                return;
            }
            adiantiHelper.alert('Atenção', 'Editar um valor administrativo pode causar problemas inesperados.').then(() => {
                group.classList.add('edit-admin-vars');
                elem.focus();
                try { elem.select(); } catch(e) {}
            });
        });

        // Config - deleta um parâmetro de configuração
        rootEvent('.delete-option', 'click', event => {
            const option_id = event.target.closest('.delete-option').getAttribute('data-id');
            adiantiHelper.confirm('Remover option', 'Deseja realmente excluir?').then(ok => { if (ok) { deleteOption(option_id); } });
        });
        
        // Edição de role - ação da opção Todos no dropdown capabilities
        const roleOptAll = event => {
            const displayOthers = event.target.checked ? 'add' : 'remove';
            event.target.closest('.tab-pane').classList[displayOthers]('all');
        };
        rootEvent('input[name="capabilities[]"][value="all"]', 'input', roleOptAll);

        // Edição de role - ação da opção Todos no dropdown group_names
        const roleOptAllGroups = event => {
            const checked = event.target.checked;
            const context = event.target.closest('.toggle-wrapper');
            $apply('input[type="checkbox"]', elem => elem.checked = checked, context);
        };
        rootEvent('input[name="group_names[]"][value="all"]', 'input', roleOptAllGroups);
        
        // ações ao abrir o painel lateral
        adiantiHelper.action.add('openSidePanel', () => {
            setTimeout(() => {
                const caps = $single('.tabpanel_form_RoleForm [name="capabilities[]"]');
                if (caps) {
                    roleOptAll({target: caps});
                }

                const field_type = $single('#form_OptionsForm [name="field_type"]');
                const edit_options = $single('#form_OptionsForm [name="edit_options"]');
                if (field_type && edit_options) {
                    showOnOption(field_type, 'combo', edit_options.closest('.form-group'));
                }
            }, 300);
        });
        
        // fechar o painel com Esc
        addEscBehavior(adiantiHelper.closePanel); // Esc
        rootEvent('#adianti_right_panel', 'click', event => { // blur
            if (!event.target.matches('#adianti_right_panel *')) {
                adiantiHelper.closePanel();
            }
        });

        // Abre o menu e marca o link atual pela URL
        setMenuStateByUrl();
        window.navigation.addEventListener("navigate", event => {
            setMenuStateByUrl(event.destination.url);
        });
        
    }, 400);
});

function deleteOption(id) {
    adiantiHelper.api.call('Options', 'restDelete', { id }).then(res => {
        const button = $single(`.delete-option[data-id="${id}"]`)
        const line = button.closest('.form-group');
        line.parentElement.removeChild(line);
    });
}

// template atual. retorna um desses: builder | lte2 | lte3 | bsb
function getMbTemplate() {
    const template = document.body.getAttribute('data-theme');
    return template ? template : '';
}

function setMenuStateByUrl(url = location.href) {
    url = removeUrlParams(['previous_class', 'previous_method', 'key', 'id'], url);
    const src = url.split('?')[1] ?? false;
    if (src) {
        const template = getMbTemplate();
        if (template == 'builder') {
            const link = $single(`.container-submenu ul li a[href$="${src}"]`);
            if (link) {
                const parent = link.closest('[module-menu]');
                const id = parent.getAttribute('module-menu');
                const parentLink = $single(`a[menu-target="${id}"]`);
                if (!parentLink.matches('.checked')) {
                    parentLink.click();
                }
                $apply('.container-submenu a.current-page', elem => elem.classList.remove('current-page'));
                link.classList.add('current-page');
            }
        }
        if (template == 'lte2') {
            const link = $single(`.sidebar-menu ul li a[href$="${src}"]`);
            if (link) {
                const parentItem = link.closest('.sidebar-menu > li.treeview');
                const parentLink = $single('a:first-child', parentItem);
                if (!parentLink.matches('.treeview.active *')) {
                    parentLink.click();
                }
                $apply('.sidebar-menu a.current-page', elem => elem.classList.remove('current-page'));
                link.classList.add('current-page');
            }
        }
        if (template == 'lte3') {
            const link = $single(`.nav-sidebar li a[href$="${src}"]`);
            if (link) {
                const parentItem = link.closest('.nav-item').parentElement.closest('.nav-item');
                const parentLink = $single('a:first-child', parentItem);
                if (!parentLink.matches('.menu-open a')) {
                    parentLink.click();
                }
                $apply('.nav-sidebar a.active', elem => elem.classList.remove('active'));
                link.classList.add('active');
            }
        }
        if (template == 'bsb') {
            const link = $single(`.sidebar .menu ul li a[href$="${src}"]`);
            if (link) {
                const parentLink = link.closest('li.x').parentElement.closest('li.x').querySelector('a.menu-toggle');
                if (!parentLink.matches('.toggled')) {
                    parentLink.click();
                }
                $apply('.sidebar .menu a.current-page', elem => elem.classList.remove('current-page'));
                link.classList.add('current-page');
            }
        }
    }
}


























