(function () {
    'use strict';

    const ROOT = '[wire\\:id]';
    const SECTION = '[wire\\:key^="builder-section-"]';
    const state = {
        tool: 'select',
        selected: null,
        multi: new Set(),
        panel: null,
        zoom: 100,
        rulers: false,
        grid: false,
        contextMenu: null,
        clipboard: null,
        ready: false,
    };

    const isBuilder = () => Boolean(document.querySelector(ROOT)) && /\\/websites\\/[^/]+\\/builder$/.test(location.pathname);
    const root = () => document.querySelector(ROOT);
    const wire = () => {
        const el = root();
        return el && window.Livewire ? window.Livewire.find(el.getAttribute('wire:id')) : null;
    };
    const sections = () => [...document.querySelectorAll(SECTION)];
    const canvas = () => document.querySelector('[data-finder-site-canvas]') || document.querySelector(SECTION)?.parentElement;
    const esc = value => String(value ?? '').replace(/[&<>\"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#039;'}[c]));
    const selected = () => state.selected;

    const meta = el => {
        const section = el?.closest(SECTION);
        if (!section) return null;
        const index = sections().indexOf(section);
        const type = section.dataset.builderSectionType || '';
        const headings = [...section.querySelectorAll('h1,h2,h3')].filter(n => !n.closest('button,a'));
        const paragraphs = [...section.querySelectorAll('p')].filter(n => !n.closest('button,a'));
        const articles = [...section.querySelectorAll('article')];
        if (headings[0] === el) return { index, slot: 'title', label: 'Título' };
        if (type === 'hero' && paragraphs[0] === el) return { index, slot: 'subtitle', label: 'Subtítulo' };
        if (type === 'hero' && paragraphs[1] === el) return { index, slot: 'description', label: 'Descrição' };
        if (paragraphs[0] === el) return { index, slot: type === 'text' ? 'body' : 'description', label: 'Texto' };
        const article = el.closest('article');
        if (article) {
            const i = articles.indexOf(article);
            const h = article.querySelector('p.font-black,h3,h4');
            const p = article.querySelector('p.mt-2,p:not(.font-black)');
            if (h === el) return { index, slot: `item.${i}.${type === 'faq' ? 'question' : type === 'testimonials' ? 'name' : 'title'}`, label: 'Título' };
            if (p === el) return { index, slot: `item.${i}.${type === 'faq' ? 'answer' : type === 'testimonials' ? 'quote' : type === 'pricing' ? 'price' : 'description'}`, label: 'Texto' };
        }
        if (el.matches('img')) return { index, slot: 'image', label: 'Imagem' };
        return null;
    };

    const getSetting = (el, key, fallback = null) => {
        const m = meta(el);
        const app = wire();
        if (!m || !app?.get) return fallback;
        try { return app.get(`sections.${m.index}.settings.elements.${m.slot}.${key}`) ?? fallback; } catch { return fallback; }
    };

    const setSetting = (el, key, value) => {
        const m = meta(el);
        const app = wire();
        if (!m || !app) return;
        try { app.set(`sections.${m.index}.settings.elements.${m.slot}.${key}`, value); } catch { /* keep local state */ }
    };

    const notify = (message) => {
        let host = document.getElementById('finder-ultimate-feedback');
        if (!host) {
            host = document.createElement('div');
            host.id = 'finder-ultimate-feedback';
            host.className = 'finder-ultimate-feedback';
            document.body.appendChild(host);
        }
        const item = document.createElement('div');
        item.textContent = message;
        host.appendChild(item);
        setTimeout(() => item.remove(), 1800);
    };

    const clearSelection = () => {
        state.multi.clear();
        document.querySelectorAll('.finder-ultimate-selected').forEach(el => el.classList.remove('finder-ultimate-selected'));
        state.selected = null;
        refreshInspector();
    };

    const select = (el, additive = false) => {
        if (!el?.closest(SECTION)) return;
        if (!additive) state.multi.clear();
        if (additive && state.multi.has(el)) state.multi.delete(el); else state.multi.add(el);
        document.querySelectorAll('.finder-ultimate-selected').forEach(node => node.classList.remove('finder-ultimate-selected'));
        state.multi.forEach(node => node.classList.add('finder-ultimate-selected'));
        state.selected = el;
        refreshInspector();
    };

    const elementCandidates = section => [
        ...section.querySelectorAll('h1,h2,h3,p,img,article,button,span'),
    ].filter(el => !el.closest('[data-finder-builder-chrome]') && (el.matches('h1,h2,h3,p,img') || el.closest('article') || el.closest('a,button')));

    const selectToolHit = event => {
        const hit = event.target?.closest('h1,h2,h3,p,img,article');
        if (!hit || !hit.closest(SECTION)) return;
        if (event.target.closest('a,button,input,textarea,select,[contenteditable="true"]') && !event.target.matches('span')) return;
        select(hit, event.shiftKey);
    };

    const styleText = (el, key, value) => {
        if (!el) return;
        el.style[key] = value;
        setSetting(el, key === 'fontSize' ? 'fontSize' : key, value);
    };

    const transform = el => {
        if (!el) return { x:0, y:0, r:0 };
        const raw = el.dataset.ultimateTransform || `${getSetting(el,'x',0) || 0},${getSetting(el,'y',0) || 0},${getSetting(el,'rotation',0) || 0}`;
        const [x,y,r] = raw.split(',').map(Number);
        return { x:Number.isFinite(x)?x:0, y:Number.isFinite(y)?y:0, r:Number.isFinite(r)?r:0 };
    };

    const applyTransform = (el, x, y, r = transform(el).r) => {
        el.dataset.ultimateTransform = `${Math.round(x)},${Math.round(y)},${Math.round(r)}`;
        el.style.position = 'relative';
        el.style.transform = `translate3d(${Math.round(x)}px,${Math.round(y)}px,0) rotate(${Math.round(r)}deg)`;
        setSetting(el,'x',Math.round(x));
        setSetting(el,'y',Math.round(y));
        setSetting(el,'rotation',Math.round(r));
    };

    const applyStored = () => {
        const app = wire();
        if (!app?.get) return;
        let data;
        try { data = app.get('sections'); } catch { return; }
        if (!Array.isArray(data)) return;
        sections().forEach((section, index) => {
            const map = data[index]?.settings?.elements || {};
            const all = [];
            const h = [...section.querySelectorAll('h1,h2,h3')].filter(n => !n.closest('button,a'));
            const p = [...section.querySelectorAll('p')].filter(n => !n.closest('button,a'));
            if (h[0]) all.push([h[0], 'title']);
            if (p[0]) all.push([p[0], section.dataset.builderSectionType === 'hero' ? 'subtitle' : section.dataset.builderSectionType === 'text' ? 'body' : 'description']);
            if (p[1]) all.push([p[1], 'description']);
            [...section.querySelectorAll('article')].forEach((article, i) => {
                const ah = article.querySelector('p.font-black,h3,h4');
                const ap = article.querySelector('p.mt-2,p:not(.font-black)');
                const type = section.dataset.builderSectionType || '';
                if (ah) all.push([ah, `item.${i}.${type === 'faq' ? 'question' : type === 'testimonials' ? 'name' : 'title'}`]);
                if (ap) all.push([ap, `item.${i}.${type === 'faq' ? 'answer' : type === 'testimonials' ? 'quote' : type === 'pricing' ? 'price' : 'description'}`]);
            });
            all.forEach(([el, slot]) => {
                const s = map[slot] || {};
                const x = Number(s.x || 0), y = Number(s.y || 0), r = Number(s.rotation || 0);
                el.dataset.ultimateTransform = `${x},${y},${r}`;
                if (x || y || r) el.style.transform = `translate3d(${x}px,${y}px,0) rotate(${r}deg)`;
                if (s.width) el.style.width = s.width;
                if (s.height && el.matches('img')) el.style.height = s.height;
                if (s.fontSize) el.style.fontSize = s.fontSize;
                if (s.color) el.style.color = s.color;
                if (s.opacity != null) el.style.opacity = s.opacity;
                if (s.textAlign) el.style.textAlign = s.textAlign;
            });
        });
    };

    const addSection = (type) => {
        const app = wire();
        if (!app?.addSection) return;
        app.addSection(type);
        notify(`${type.replaceAll('_',' ')} adicionado`);
    };

    const actionForTool = tool => {
        if (tool === 'text') return addSection('text');
        if (tool === 'image') return addSection('image');
        if (tool === 'button') return addSection('button');
        if (tool === 'card') return addSection('card');
        if (tool === 'gallery') return addSection('gallery');
        if (tool === 'video') return addSection('video');
        if (tool === 'form') return addSection('contact_form');
        if (tool === 'products') return addSection('product_grid');
        if (tool === 'pricing') return addSection('pricing');
        if (tool === 'faq') return addSection('faq');
        if (tool === 'template') {
            document.querySelector('[data-builder-open-templates]')?.click();
            document.querySelector('[data-builder-template-modal]')?.classList.remove('hidden');
            return;
        }
        if (tool === 'grid') { state.grid = !state.grid; document.body.classList.toggle('finder-ultimate-grid',state.grid); return; }
        if (tool === 'rulers') { state.rulers = !state.rulers; document.body.classList.toggle('finder-ultimate-rulers',state.rulers); return; }
    };

    const buildSidebar = () => {
        if (document.getElementById('finder-ultimate-sidebar')) return;
        const sidebar = document.createElement('aside');
        sidebar.id = 'finder-ultimate-sidebar';
        sidebar.setAttribute('data-finder-builder-chrome','true');
        sidebar.innerHTML = `
            <div class="fu-brand"><span>F</span><b>Studio</b></div>
            <div class="fu-tools">
                <button data-tool="select" class="is-active"><strong>⌁</strong><span>Selecionar</span></button>
                <button data-tool="template"><strong>▦</strong><span>Modelos</span></button>
                <button data-tool="text"><strong>T</strong><span>Texto</span></button>
                <button data-tool="image"><strong>◩</strong><span>Imagem</span></button>
                <button data-tool="card"><strong>▭</strong><span>Card</span></button>
                <button data-tool="products"><strong>⌘</strong><span>Produtos</span></button>
                <button data-tool="gallery"><strong>▧</strong><span>Galeria</span></button>
                <button data-tool="video"><strong>▶</strong><span>Vídeo</span></button>
                <button data-tool="form"><strong>✉</strong><span>Contacto</span></button>
                <button data-tool="pricing"><strong>€</strong><span>Preços</span></button>
                <button data-tool="faq"><strong>?</strong><span>FAQ</span></button>
                <button data-tool="grid"><strong>▦</strong><span>Guias</span></button>
                <button data-tool="rulers"><strong>⊞</strong><span>Réguas</span></button>
            </div>
            <div class="fu-bottom"><button data-open-design><strong>✦</strong><span>Design</span></button><button data-open-layers><strong>≡</strong><span>Camadas</span></button></div>
        `;
        document.body.appendChild(sidebar);
        sidebar.querySelectorAll('[data-tool]').forEach(button => button.addEventListener('click', () => {
            sidebar.querySelectorAll('[data-tool]').forEach(b => b.classList.remove('is-active'));
            button.classList.add('is-active');
            state.tool = button.dataset.tool;
            actionForTool(state.tool);
        }));
        sidebar.querySelector('[data-open-design]').addEventListener('click',()=>document.getElementById('finder-builder-canva-pro-lab')?.classList.remove('hidden'));
        sidebar.querySelector('[data-open-layers]').addEventListener('click',()=>document.getElementById('finder-builder-canva-layers')?.classList.remove('hidden'));
    };

    const control = (label, html, onInput) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'fu-control';
        wrapper.innerHTML = `<label>${esc(label)}</label>${html}`;
        const field = wrapper.querySelector('input,select,button');
        if (field && onInput) field.addEventListener(field.tagName === 'SELECT' ? 'change' : 'input', () => onInput(field.value));
        return wrapper;
    };

    const refreshInspector = () => {
        let panel = document.getElementById('finder-ultimate-inspector');
        if (!panel) {
            panel = document.createElement('aside');
            panel.id='finder-ultimate-inspector';
            panel.setAttribute('data-finder-builder-chrome','true');
            document.body.appendChild(panel);
        }
        const el = selected();
        panel.innerHTML='';
        if (!el) {
            panel.innerHTML = `<div class="fu-empty"><div class="fu-empty-icon">✦</div><b>Nada seleccionado</b><p>Seleciona um elemento no canvas para editar propriedades.</p><div class="fu-shortcuts"><span>V Selecionar</span><span>T Texto</span><span>Esc Fechar</span><span>Del Eliminar</span></div></div>`;
            return;
        }
        const m = meta(el);
        const section = el.closest(SECTION);
        const sIndex = sections().indexOf(section);
        const font = getSetting(el,'fontSize',getComputedStyle(el).fontSize);
        const color = getSetting(el,'color','#18181b');
        const opacity = getSetting(el,'opacity',getComputedStyle(el).opacity);
        const align = getSetting(el,'textAlign',getComputedStyle(el).textAlign);
        const t = transform(el);
        panel.innerHTML = `<div class="fu-inspector-head"><div><small>ELEMENTO</small><b>${esc(m?.label || el.tagName.toLowerCase())}</b></div><button data-close>×</button></div><div class="fu-tabs"><button data-tab="design" class="is-active">Design</button><button data-tab="position">Posição</button><button data-tab="advanced">Avançado</button></div><div data-inspector-body></div>`;
        const body = panel.querySelector('[data-inspector-body]');
        const design = document.createElement('div');
        design.appendChild(control('Tamanho da letra', `<input type="range" min="8" max="120" step="1" value="${parseFloat(font)||16}">`, value=>{ el.style.fontSize=`${value}px`; setSetting(el,'fontSize',`${value}px`); }));
        design.appendChild(control('Alinhamento', `<div class="fu-segment"><button data-v="left">Esq.</button><button data-v="center">Centro</button><button data-v="right">Dir.</button></div>`, value=>{}));
        design.querySelectorAll('.fu-segment button').forEach(btn=>btn.addEventListener('click',()=>{el.style.textAlign=btn.dataset.v;setSetting(el,'textAlign',btn.dataset.v);design.querySelectorAll('button').forEach(b=>b.classList.remove('is-active'));btn.classList.add('is-active');}));
        design.appendChild(control('Cor', `<input type="color" value="${/^#[0-9a-f]{6}$/i.test(color)?color:'#18181b'}">`, value=>{el.style.color=value;setSetting(el,'color',value);}));
        design.appendChild(control('Opacidade', `<input type="range" min="0.1" max="1" step="0.05" value="${Number(opacity)||1}">`, value=>{el.style.opacity=value;setSetting(el,'opacity',String(value));}));
        design.innerHTML += `<div class="fu-action-grid"><button data-style="bold">B</button><button data-style="italic">I</button><button data-style="underline">U</button><button data-style="reset">↺</button></div>`;
        design.querySelectorAll('[data-style]').forEach(btn=>btn.addEventListener('click',()=>{
            if(btn.dataset.style==='bold') el.style.fontWeight = getComputedStyle(el).fontWeight >= 600 ? '400' : '700';
            if(btn.dataset.style==='italic') el.style.fontStyle = getComputedStyle(el).fontStyle==='italic' ? 'normal' : 'italic';
            if(btn.dataset.style==='underline') el.style.textDecoration = getComputedStyle(el).textDecorationLine==='underline' ? 'none' : 'underline';
            if(btn.dataset.style==='reset') { el.style.fontSize=''; el.style.color=''; el.style.opacity=''; el.style.textAlign=''; applyTransform(el,0,0,0); }
        }));
        const pos = document.createElement('div'); pos.className='hidden';
        pos.innerHTML = `<div class="fu-coords"><label>X<input data-pos="x" type="number" value="${Math.round(t.x)}"></label><label>Y<input data-pos="y" type="number" value="${Math.round(t.y)}"></label></div><div class="fu-coords"><label>Largura<input data-pos="w" type="number" value="${Math.round(el.getBoundingClientRect().width)}"></label><label>Altura<input data-pos="h" type="number" value="${Math.round(el.getBoundingClientRect().height)}"></label></div><div class="fu-control"><label>Rotação</label><input data-pos="r" type="range" min="-180" max="180" value="${Math.round(t.r)}"></div><div class="fu-action-grid"><button data-posact="left">←</button><button data-posact="center">⌗</button><button data-posact="right">→</button><button data-posact="up">↑</button><button data-posact="down">↓</button></div>`;
        pos.querySelectorAll('[data-pos]').forEach(input=>input.addEventListener('input',()=>{
            const p=input.dataset.pos;
            if(p==='x'||p==='y'||p==='r'){const n=transform(el); applyTransform(el,p==='x'?Number(input.value):n.x,p==='y'?Number(input.value):n.y,p==='r'?Number(input.value):n.r);} 
            if(p==='w'){el.style.width=`${Number(input.value)}px`;setSetting(el,'width',el.style.width);} 
            if(p==='h'&&el.matches('img')){el.style.height=`${Number(input.value)}px`;setSetting(el,'height',el.style.height);} 
        }));
        pos.querySelectorAll('[data-posact]').forEach(btn=>btn.addEventListener('click',()=>{
            const n=transform(el); if(btn.dataset.posact==='left') applyTransform(el,n.x-8,n.y,n.r); if(btn.dataset.posact==='right') applyTransform(el,n.x+8,n.y,n.r); if(btn.dataset.posact==='up') applyTransform(el,n.x,n.y-8,n.r); if(btn.dataset.posact==='down') applyTransform(el,n.x,n.y+8,n.r); if(btn.dataset.posact==='center') applyTransform(el,0,0,n.r);
        }));
        const adv = document.createElement('div'); adv.className='hidden';
        adv.innerHTML = `<div class="fu-action-list"><button data-command="duplicate">Duplicar elemento</button><button data-command="front">Trazer para a frente</button><button data-command="back">Enviar para trás</button><button data-command="hide">Ocultar</button><button data-command="lock">Bloquear</button><button data-command="delete">Eliminar</button></div>`;
        adv.querySelectorAll('[data-command]').forEach(btn=>btn.addEventListener('click',()=>advancedAction(btn.dataset.command,el)));
        body.append(design,pos,adv);
        panel.querySelector('[data-close]').addEventListener('click',clearSelection);
        panel.querySelectorAll('[data-tab]').forEach(tab=>tab.addEventListener('click',()=>{
            panel.querySelectorAll('[data-tab]').forEach(t=>t.classList.remove('is-active')); tab.classList.add('is-active');
            design.classList.toggle('hidden',tab.dataset.tab!=='design'); pos.classList.toggle('hidden',tab.dataset.tab!=='position'); adv.classList.toggle('hidden',tab.dataset.tab!=='advanced');
        }));
    };

    const advancedAction = (action, el) => {
        const section = el.closest(SECTION);
        if (action === 'front') { el.style.zIndex = String((Number(getComputedStyle(el).zIndex)||0)+1); setSetting(el,'zIndex',el.style.zIndex); }
        if (action === 'back') { el.style.zIndex = String((Number(getComputedStyle(el).zIndex)||0)-1); setSetting(el,'zIndex',el.style.zIndex); }
        if (action === 'hide') { el.style.visibility = 'hidden'; setSetting(el,'visibility','hidden'); }
        if (action === 'lock') { el.dataset.finderLocked = el.dataset.finderLocked === '1' ? '0':'1'; notify(el.dataset.finderLocked==='1'?'Elemento bloqueado':'Elemento desbloqueado'); }
        if (action === 'delete') { el.remove(); notify('Elemento removido do canvas'); }
        if (action === 'duplicate') {
            const clone = el.cloneNode(true);
            clone.dataset.ultimateTransform = `${transform(el).x+24},${transform(el).y+24},${transform(el).r}`;
            clone.style.transform=`translate3d(${transform(el).x+24}px,${transform(el).y+24}px,0) rotate(${transform(el).r}deg)`;
            el.parentElement?.insertBefore(clone,el.nextSibling);
            select(clone);
            notify('Elemento duplicado');
        }
        if (section && action === 'delete' && section.querySelectorAll('h1,h2,h3,p,img,article').length===0) {
            const app=wire(); app?.removeSection?.(sections().indexOf(section));
        }
    };

    const buildContextMenu = () => {
        if (state.contextMenu) return state.contextMenu;
        const menu = document.createElement('div');
        menu.id='finder-ultimate-context';
        menu.setAttribute('data-finder-builder-chrome','true');
        menu.innerHTML='<button data-act="copy">Copiar estilo</button><button data-act="paste">Colar estilo</button><button data-act="duplicate">Duplicar</button><button data-act="front">Trazer para a frente</button><button data-act="back">Enviar para trás</button><button data-act="delete">Eliminar</button>';
        menu.querySelectorAll('button').forEach(btn=>btn.addEventListener('click',()=>{const el=selected();if(!el){menu.classList.remove('is-open');return;}const a=btn.dataset.act;if(a==='copy'){state.clipboard={fontSize:el.style.fontSize,color:el.style.color,opacity:el.style.opacity,textAlign:el.style.textAlign,transform:transform(el)};notify('Estilo copiado');}if(a==='paste'&&state.clipboard){el.style.fontSize=state.clipboard.fontSize;el.style.color=state.clipboard.color;el.style.opacity=state.clipboard.opacity;el.style.textAlign=state.clipboard.textAlign;const t=state.clipboard.transform;applyTransform(el,t.x,t.y,t.r);notify('Estilo colado');}if(a==='duplicate')advancedAction('duplicate',el);if(a==='front')advancedAction('front',el);if(a==='back')advancedAction('back',el);if(a==='delete')advancedAction('delete',el);menu.classList.remove('is-open');}));
        document.body.appendChild(menu); state.contextMenu=menu; return menu;
    };

    const zoomControls = () => {
        if (document.getElementById('finder-ultimate-zoom')) return;
        const host=document.createElement('div');host.id='finder-ultimate-zoom';host.setAttribute('data-finder-builder-chrome','true');
        host.innerHTML='<button data-z="minus">−</button><span>100%</span><button data-z="plus">+</button><button data-z="fit">⌗</button>';
        host.querySelectorAll('[data-z]').forEach(btn=>btn.addEventListener('click',()=>{const a=btn.dataset.z;if(a==='minus')state.zoom=Math.max(50,state.zoom-10);if(a==='plus')state.zoom=Math.min(150,state.zoom+10);if(a==='fit')state.zoom=100;host.querySelector('span').textContent=state.zoom+'%';const frame=canvas()?.parentElement; if(frame) frame.style.zoom=state.zoom/100;}));
        document.body.appendChild(host);
    };

    const keyboard = event => {
        if (!isBuilder()) return;
        const typing = event.target?.isContentEditable || ['INPUT','TEXTAREA','SELECT'].includes(event.target?.tagName);
        const mod=event.ctrlKey||event.metaKey;
        if (!typing && event.key.toLowerCase()==='v') { state.tool='select'; return; }
        if (!typing && event.key.toLowerCase()==='t') { event.preventDefault();addSection('text');return; }
        if (!typing && event.key==='Escape') { clearSelection(); document.getElementById('finder-ultimate-context')?.classList.remove('is-open'); return; }
        if (!typing && event.key==='Delete' && selected()) { event.preventDefault(); advancedAction('delete',selected()); return; }
        if (!typing && mod && event.key.toLowerCase()==='d' && selected()) { event.preventDefault(); advancedAction('duplicate',selected()); return; }
        if (!typing && mod && event.key.toLowerCase()==='c' && selected()) { event.preventDefault(); state.clipboard={fontSize:selected().style.fontSize,color:selected().style.color,opacity:selected().style.opacity,textAlign:selected().style.textAlign,transform:transform(selected())}; notify('Estilo copiado'); return; }
        if (!typing && mod && event.key.toLowerCase()==='v' && selected() && state.clipboard) { event.preventDefault(); const el=selected();el.style.fontSize=state.clipboard.fontSize;el.style.color=state.clipboard.color;el.style.opacity=state.clipboard.opacity;el.style.textAlign=state.clipboard.textAlign;const t=state.clipboard.transform;applyTransform(el,t.x,t.y,t.r);notify('Estilo colado');return; }
        if (typing || !selected()) return;
        const step=event.shiftKey?10:1; const t=transform(selected());
        if(event.key==='ArrowLeft'){event.preventDefault();applyTransform(selected(),t.x-step,t.y,t.r);} 
        if(event.key==='ArrowRight'){event.preventDefault();applyTransform(selected(),t.x+step,t.y,t.r);} 
        if(event.key==='ArrowUp'){event.preventDefault();applyTransform(selected(),t.x,t.y-step,t.r);} 
        if(event.key==='ArrowDown'){event.preventDefault();applyTransform(selected(),t.x,t.y+step,t.r);} 
    };

    const install = () => {
        if (!isBuilder()) return;
        buildSidebar();
        refreshInspector();
        zoomControls();
        applyStored();
        if (!state.ready) { notify('Finder Studio · modo de edição visual ativo'); state.ready=true; }
    };

    document.addEventListener('click', event => {
        if (!isBuilder()) return;
        if (event.target.closest('[data-finder-builder-chrome]')) return;
        if (state.tool === 'select') selectToolHit(event);
        else if (state.tool === 'select' && !event.target.closest(SECTION)) clearSelection();
    }, true);

    document.addEventListener('contextmenu', event => {
        if (!isBuilder() || !selected()) return;
        if (!event.target.closest(SECTION)) return;
        event.preventDefault();
        const menu=buildContextMenu(); menu.style.left=`${Math.min(innerWidth-230,event.clientX)}px`;menu.style.top=`${Math.min(innerHeight-270,event.clientY)}px`;menu.classList.add('is-open');
    });

    document.addEventListener('keydown', keyboard);
    window.addEventListener('resize', ()=>refreshInspector(), {passive:true});
    document.addEventListener('scroll', ()=>{}, {passive:true});

    if (window.Livewire) {
        install();
        Livewire.hook('morph.updated',()=>setTimeout(install,60));
    } else document.addEventListener('livewire:init',()=>install(),{once:true});
})();
