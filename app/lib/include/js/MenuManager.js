
window.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {

        // Editar menu - edita um ícone
        rootEvent('[page-name="MenuManager"] .edit-icon', 'click', event => {
            const a = event.target.closest('.edit-icon');
            const ico = $single('span', a)?.className || '';
            const mod = a.getAttribute('data-module');
            const itm = a.getAttribute('data-item');
            editIconForm(ico, mod, itm);
        });

        // Editar menu - aplica a edição do ícone
        rootEvent('[page-name="MenuManager"] .add-item', 'click', event => {
            event.preventDefault();
            addNewItemFields(event.target);
        });

        // Editar menu - deleta um item
        rootEvent('[page-name="MenuManager"] .delete-item', 'click', event => {
            event.preventDefault();
            removeMenuItem(event);
        });

        // Editar menu - edita o rótulo de um item
        rootEvent('[page-name="MenuManager"] .edit-item', 'click', event => {
            event.preventDefault();
            editMenuItem(event);
        });

        // Editar menu - edita o ícone de um item
        rootEvent('.popup-popup .iconpicker-item', 'click', event => {
            event.preventDefault();
            $single('.popup-popup .input-group-addon').click();
        });

        // Editar menu - salva o menu
        rootEvent('#save_button', 'click', event => {
            const obj = getMenuOrder();
            const jsn = JSON.stringify(obj);
            adiantiHelper.wait(true);
            adiantiHelper.api.call('MenuManager', 'saveMenu', { menu: jsn }).then(res => {
                adiantiHelper.wait(false);
                if (res.data.message) {
                    rootEvent('.modal-footer .btn-default', 'mousedown', () => location.reload());
                    adiantiHelper.alert("Salvar menu", res.data.message);
                }
            });
        });

        // Editar menu - reseta o menu
        rootEvent('#reset_button', 'click', event => {
            adiantiHelper.wait(true);
            adiantiHelper.api.call('MenuManager', 'resetMenu').then(res => {
                adiantiHelper.wait(false);
                if (res.data.message) {
                    rootEvent('.modal-footer .btn-default', 'mousedown', () => location.reload());
                    adiantiHelper.alert("Restaurar menu", res.data.message);
                }
            });
        });

    }, 300);
});


function getMenuOrder() {
    let menu = [];
    const modules = $list('ul.module-menu > li.menu-item');
    Array.from(modules).forEach(mod => {
        let obj = menuItemObj(mod);
        const submenu = $single('ul', mod);
        if (submenu) {
            obj.menu = [];
            $apply('li.menu-item', li => {
                obj.menu.push(menuItemObj(li));
            }, submenu);
        }
        menu.push(obj);
    });
    return menu;
}

function menuItemObj(itm) {
    const label = $single('.module-label,.item-label', itm).innerHTML.trim();
    const icon = ($single('.edit-icon span', itm)?.className || '').replace(/^(fa[rs]?) fa-(.+)$/, '$1:$2') + ' fa-fw';
    const a = $single('a.edit-icon', itm);
    const moduleIndex = a.getAttribute('data-module');
    const itemIndex = a.getAttribute('data-item');
    let obj = { label, icon, moduleIndex, itemIndex };
    const act = itm.getAttribute('data-action');
    if (act) {
        obj.action = act;
    }
    return obj;
}

function addNewItemFields(link) {
    const li = link.closest('li');
    const ul = li.closest('ul');
    const label = tag('input', { type: 'text', id: 'new_item_label', placeholder: 'Label', style: 'font-size: 12px; padding: 0 3px; margin-right: 3px' });
    const action = tag('input', { type: 'text', id: 'new_item_action', placeholder: 'Action', style: 'font-size: 12px; padding: 0 3px;' });
    const del = event => {
        const parent = event.target.closest('li');
        parent.parentElement.removeChild(parent);
    };
    $apply('li.new-item', li => del({ target: li }));
    const nli = tag('li', { class: 'new-item-fields' }, label);
    nli.className = 'new-item';
    nli.appendChild(label);
    nli.appendChild(action);
    
    const add = tag('a', { class: 'add-menu-item', style: 'padding: 2px; margin: 0 3px; color: green; font-size: 16px; cursor: pointer;' }, '<span class="fa fa-check"></span>');
    add.addEventListener('click', event => {
        const lbl = $single('#new_item_label');
        const act = $single('#new_item_action');
        if (lbl && lbl.value) {
            addMenuItem(lbl, act);
        }
    });
    const cancel = tag('a', { class: 'cancel-menu-item', style: 'padding: 2px; margin: 0 3px; color: red; font-size: 16px; cursor: pointer;' }, '<span class="fa fa-times"></span>');
    cancel.addEventListener('click', event => {
        del(event);
    });
    nli.appendChild(add);
    nli.appendChild(cancel);
    
    ul.insertBefore(nli, li);
    label.focus();
}

function removeMenuItem(event) {
    const msg = event.target.matches('.menu-item .menu-item *') ? 'Deseja remover este item?' : 'Deseja remover este módulo?';
    adiantiHelper.confirm("Remover item", msg).then(res => {
        if (res) {
            const li = event.target.closest('.menu-item');
            li.parentElement.removeChild(li);
        }
    });
}

function editMenuItem(event) {
    const itm = event.target.closest('.menu-item');
    const label = itm.querySelector('div');
    const act = itm.getAttribute('data-action');
    const module = itm.getAttribute('data-module');
    const item = itm.getAttribute('data-item');
    
    const form = tag('form', { 'class': 'edit-item-form', 'action': '#' });
    const row1 = tag('div', { 'class': 'row', 'style': 'margin: 0.5rem auto;' });
    row1.appendChild(tag('div', { 'class': 'col-sm-4' }, tag('label', { 'for': 'item_name' }, 'Rótulo')));
    row1.appendChild(tag('div', { 'class': 'col-sm-8' }, tag('input', { 'id': 'item_name', 'value': label.innerHTML.trim(), 'style': 'width: 100%;' })));
    form.appendChild(row1);
    const row2 = tag('div', { 'class': 'row', 'style': 'margin: 0.5rem auto;' });
    row2.appendChild(tag('div', { 'class': 'col-sm-4' }, tag('label', { 'for': 'item_action' }, 'Ação')));
    row2.appendChild(tag('div', { 'class': 'col-sm-8' }, tag('input', { 'id': 'item_action', 'value': act, 'style': 'width: 100%;' })));
    form.appendChild(row2);
    
    const pop = new Popup('Editar item', form);
    pop.footer = true;
    const btn = tag('button', { class: 'save-item' }, 'Salvar');
    btn.setAttribute('data-module', module);
    btn.setAttribute('data-item', item);
    btn.addEventListener('click', event => {
        const mod = event.target.getAttribute('data-module');
        const itm = event.target.getAttribute('data-item');
        const elem = $single(`.menu-item[data-module="${mod}"][data-item="${itm}"]`);
        if (elem) {
            $single('.item-label,.module-label', elem).innerHTML = $single('.popup-popup #item_name').value;
            elem.setAttribute('data-action', $single('#item_action').value);
        }
        pop.close();
    });
    pop.addFooterButton(btn);
    pop.open();
}

function addMenuItem(lbl, act) {
    const li = lbl.closest('li.new-item');
    const label = lbl.value;
    const action = act.value;
    let item = getIndex(lbl.closest('.module-submenu > li'));
    const module = getIndex(lbl.closest('.module-menu > li'));
    const nli = tag(
        'li',
        { class: 'menu-item', 'data-module': module, 'data-item': item, 'data-action': action },
        `<a href="javascript://" class="edit-icon" data-module="${module}" data-item="${item}">[+]</a>`
    );
    const buttons = '<a href="#" class="edit-item"><span class="fa fa-edit" style="color: lightblue;"></span></a> ' +
                    '<a href="#" class="delete-item"><span class="fa fa-minus" style="color: red;"></span></a>';
    // if (item < 0) {
        nli.innerHTML = nli.innerHTML + ` <div class="module-label" style="display: inline-block;">${label}</div> ${buttons}`;
        if (!action) {
        nli.innerHTML = nli.innerHTML + ` <ul class="module-submenu"><li class="add-item"><a href="#"><span class="fa fa-plus" style="color: red;"></span> Novo item</a></li></ul>`;
        }
    // } else {
    //     nli.innerHTML = nli.innerHTML + ` <div class="item-label" style="display: inline-block;">${label}</div> ${buttons}`;
    // }
    li.replaceWith(nli);
}

function editIconForm(icon, module, item = -1) {
    const content = $single('.icon-popup .row');
    const input = $single('.iconpicker-input', content);
    input.value = icon;
    const iid = input.id;
    $single('.input-group-addon i', content).className = icon;
    const pop = new Popup('Editar ícone', content);
    pop.footer = true;
    const btn = tag('button', { class: 'save-icon' }, 'Salvar');
    btn.setAttribute('data-module', module);
    btn.setAttribute('data-item', item);
    btn.addEventListener('click', event => {
        const ico = $single('.iconpicker-input', content).value;
        const mod = event.target.getAttribute('data-module');
        const itm = event.target.getAttribute('data-item');
        const a = $single(`.edit-icon[data-module="${mod}"][data-item="${itm}"]`);
        if (a) {
            a.innerHTML = `<span class="${ico}"></span>`;
        }
        pop.close();
    });
    pop.addFooterButton(btn);
    pop.open();
    pop.on('close', content => {
        $single('.icon-popup').appendChild(content);
    });
    setTimeout(() => ticon_start(iid, false), 50);
}