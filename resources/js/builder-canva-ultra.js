(function () {
    'use strict';

    const ROOT = '[wire\\:id]';
    const SECTION = '[wire\\:key^="builder-section-"]';
    const state = {
        element: null,
        section: null,
        drag: null,
        resize: null,
        rotate: null,
        handles: null,
        toolbar: null,
        guides: null,
        snap: 8,
        clipboard: null,
        listeners: false,
    };

    const isBuilder = () => Boolean(document.querySelector(ROOT)) && /\/websites\/[^/]+\/builder$/.test(location.pathname);
    const root = () => document.querySelector(ROOT);
    const wire = () => {
        const node = root();
        return node && window.Livewire ? window.Livewire.find(node.getAttribute('wire:id')) : null;
    };
    const sections = () => [...document.querySelectorAll(SECTION)];

    const meta = element => {
        const section = element?.closest(SECTION);
        if (!section) return null;
        const index = sections().indexOf(section);
        const type = section.dataset.builderSectionType || '';
        const headings = [...section.querySelectorAll('h1,h2,h3')].filter(n => !n.closest('button,a'));
        const paragraphs = [...section.querySelectorAll('p')].filter(n => !n.closest('button,a'));
        const articles = [...section.querySelectorAll('article')];
        if (headings[0] === element) return { index, slot: 'title' };
        if (type === 'hero' && paragraphs[0] === element) return { index, slot: 'subtitle' };
        if (type === 'hero' && paragraphs[1] === element) return { index, slot: 'description' };
        if (paragraphs[0] === element) return { index, slot: type === 'text' ? 'body' : 'description' };
        const article = element.closest('article');
        if (article) {
            const item = articles.indexOf(article);
            const h = article.querySelector('p.font-black,h3,h4');
            const p = article.querySelector('p.mt-2,p:not(.font-black)');
            if (h === element) return { index, slot: `item.${item}.${type === 'faq' ? 'question' : type === 'testimonials' ? 'name' : 'title'}` };
            if (p === element) return { index, slot: `item.${item}.${type === 'faq' ? 'answer' : type === 'testimonials' ? 'quote' : type === 'pricing' ? 'price' : 'description'}` };
        }
        if (element.matches('img')) return { index, slot: 'image' };
        return null;
    };

    const setting = (element, key, fallback = null) => {
        const m = meta(element);
        const app = wire();
        if (!m || !app?.get) return fallback;
        try { return app.get(`sections.${m.index}.settings.elements.${m.slot}.${key}`) ?? fallback; } catch { return fallback; }
    };

    const persist = (element, key, value) => {
        const m = meta(element);
        const app = wire();
        if (!m || !app) return;
        try { app.set(`sections.${m.index}.settings.elements.${m.slot}.${key}`, value); } catch { /* visual state remains */ }
    };

    const nums = element => {
        const t = element.dataset.canvaTransform || '0,0,0';
        const [x,y,r] = t.split(',').map(Number);
        return { x: Number.isFinite(x) ? x : 0, y: Number.isFinite(y) ? y : 0, r: Number.isFinite(r) ? r : 0 };
    };

    const writeTransform = (element, x, y, r = nums(element).r) => {
        element.dataset.canvaTransform = `${Math.round(x)},${Math.round(y)},${Math.round(r)}`;
        element.style.position = 'relative';
        element.style.transform = `translate3d(${Math.round(x)}px,${Math.round(y)}px,0) rotate(${r}deg)`;
        persist(element, 'x', Math.round(x));
        persist(element, 'y', Math.round(y));
        persist(element, 'rotation', Math.round(r));
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
                el.dataset.canvaTransform = `${x},${y},${r}`;
                if (x || y || r) {
                    el.style.position = 'relative';
                    el.style.transform = `translate3d(${x}px,${y}px,0) rotate(${r}deg)`;
                }
                if (s.width) el.style.width = s.width;
                if (s.height) el.style.height = s.height;
                if (s.opacity != null) el.style.opacity = s.opacity;
            });
        });
    };

    const elementFromPoint = event => {
        const target = event.target?.closest('.finder-canva-editable,.finder-canva-image,h1,h2,h3,p,img,article');
        if (!target) return null;
        const section = target.closest(SECTION);
        return section ? target : null;
    };

    const clear = () => {
        document.querySelectorAll('.finder-canva-ultra-selected').forEach(n => n.classList.remove('finder-canva-ultra-selected'));
        state.element = null;
        state.section = null;
        hideHandles();
    };

    const select = (element) => {
        if (!element || !element.closest(SECTION)) return;
        document.querySelectorAll('.finder-canva-ultra-selected').forEach(n => n.classList.remove('finder-canva-ultra-selected'));
        state.element = element;
        state.section = element.closest(SECTION);
        element.classList.add('finder-canva-ultra-selected');
        ensurePosition(element);
        showHandles();
        positionUI();
    };

    const ensurePosition = element => {
        if (!element.dataset.canvaTransform) {
            const x = Number(setting(element,'x',0) || 0);
            const y = Number(setting(element,'y',0) || 0);
            const r = Number(setting(element,'rotation',0) || 0);
            element.dataset.canvaTransform = `${x},${y},${r}`;
        }
    };

    const canvas = () => document.querySelector('[data-finder-site-canvas]') || document.querySelector(`${SECTION}`)?.parentElement;

    const createOverlay = () => {
        if (!document.getElementById('finder-canva-ultra-overlay')) {
            const overlay = document.createElement('div');
            overlay.id = 'finder-canva-ultra-overlay';
            overlay.innerHTML = '<div data-handle="nw"></div><div data-handle="n"></div><div data-handle="ne"></div><div data-handle="e"></div><div data-handle="se"></div><div data-handle="s"></div><div data-handle="sw"></div><div data-handle="w"></div><div data-handle="rotate">↻</div>';
            document.body.appendChild(overlay);
            state.handles = overlay;
            overlay.querySelectorAll('[data-handle]').forEach(handle => {
                handle.addEventListener('pointerdown', startHandle);
            });
        }
        if (!document.getElementById('finder-canva-ultra-toolbar')) {
            const toolbar = document.createElement('div');
            toolbar.id = 'finder-canva-ultra-toolbar';
            toolbar.innerHTML = '<button data-act="duplicate">⧉</button><button data-act="front">↑</button><button data-act="back">↓</button><button data-act="rotate-left">↺</button><button data-act="rotate-right">↻</button><button data-act="center">⌗</button><button data-act="reset">Reset</button>';
            toolbar.querySelectorAll('button').forEach(button => button.addEventListener('click', () => toolbarAction(button.dataset.act)));
            document.body.appendChild(toolbar);
            state.toolbar = toolbar;
        }
    };

    const hideHandles = () => {
        state.handles?.classList.remove('is-visible');
        state.toolbar?.classList.remove('is-visible');
    };

    const positionUI = () => {
        if (!state.element || !document.body.contains(state.element)) return hideHandles();
        createOverlay();
        const rect = state.element.getBoundingClientRect();
        const overlay = state.handles;
        overlay.style.left = `${rect.left - 2}px`;
        overlay.style.top = `${rect.top - 2}px`;
        overlay.style.width = `${rect.width + 4}px`;
        overlay.style.height = `${rect.height + 4}px`;
        overlay.classList.add('is-visible');
        const toolbarWidth = 330;
        state.toolbar.style.left = `${Math.max(8, Math.min(innerWidth - toolbarWidth - 8, rect.left + rect.width/2 - toolbarWidth/2))}px`;
        state.toolbar.style.top = `${Math.max(76, rect.top - 48)}px`;
        state.toolbar.classList.add('is-visible');
    };

    const snapValue = value => Math.round(value / state.snap) * state.snap;

    const nearest = (value, target) => Math.abs(value-target) <= state.snap ? target : value;

    const snapDrag = (x, y, element) => {
        const host = canvas();
        if (!host) return {x,y};
        const rect = host.getBoundingClientRect();
        const er = element.getBoundingClientRect();
        const centerX = er.left + er.width/2;
        const centerY = er.top + er.height/2;
        const left = er.left;
        const top = er.top;
        let nextX = x, nextY = y;
        const hostCenterX = rect.left + rect.width/2;
        const hostCenterY = rect.top + rect.height/2;
        const dxCenter = hostCenterX-centerX;
        const dyCenter = hostCenterY-centerY;
        if (Math.abs(dxCenter) <= state.snap) nextX += dxCenter;
        if (Math.abs(dyCenter) <= state.snap) nextY += dyCenter;
        if (Math.abs(rect.left-left) <= state.snap) nextX += rect.left-left;
        if (Math.abs(rect.top-top) <= state.snap) nextY += rect.top-top;
        return {x:snapValue(nextX),y:snapValue(nextY)};
    };

    const startDrag = event => {
        if (!state.element) return;
        if (event.button !== 0) return;
        if (event.target.closest('button,a,input,textarea,select,[contenteditable="true"]')) return;
        ensurePosition(state.element);
        state.drag = { id: event.pointerId, sx:event.clientX, sy:event.clientY, start:nums(state.element) };
        state.element.setPointerCapture?.(event.pointerId);
        event.preventDefault();
    };

    const moveDrag = event => {
        if (!state.drag || !state.element) return;
        const scale = 1;
        let x = state.drag.start.x + (event.clientX-state.drag.sx)/scale;
        let y = state.drag.start.y + (event.clientY-state.drag.sy)/scale;
        if (!event.altKey) ({x,y} = snapDrag(x,y,state.element));
        writeTransform(state.element,x,y,state.drag.start.r);
        positionUI();
    };

    const endDrag = () => { state.drag = null; };

    const startHandle = event => {
        if (!state.element) return;
        const handle = event.currentTarget.dataset.handle;
        ensurePosition(state.element);
        if (handle === 'rotate') {
            state.rotate = { sx:event.clientX, sy:event.clientY, rect:state.element.getBoundingClientRect(), start:nums(state.element) };
        } else {
            const rect=state.element.getBoundingClientRect();
            state.resize={handle,sx:event.clientX,sy:event.clientY,startW:rect.width,startH:rect.height,startTransform:nums(state.element),font:parseFloat(getComputedStyle(state.element).fontSize)||16};
        }
        event.currentTarget.setPointerCapture?.(event.pointerId);
        event.preventDefault(); event.stopPropagation();
    };

    const moveHandle = event => {
        if (state.rotate && state.element) {
            const r=state.rotate.rect;
            const cx=r.left+r.width/2, cy=r.top+r.height/2;
            const a1=Math.atan2(state.rotate.sy-cy,state.rotate.sx-cx);
            const a2=Math.atan2(event.clientY-cy,event.clientX-cx);
            let deg=state.rotate.start.r+(a2-a1)*180/Math.PI;
            if (!event.altKey) deg=Math.round(deg/5)*5;
            writeTransform(state.element,state.rotate.start.x,state.rotate.start.y,deg);
            positionUI(); return;
        }
        if (!state.resize || !state.element) return;
        const dX=event.clientX-state.resize.sx, dY=event.clientY-state.resize.sy;
        let w=state.resize.startW, h=state.resize.startH;
        const handle=state.resize.handle;
        if (handle.includes('e')) w+=dX;
        if (handle.includes('w')) w-=dX;
        if (handle.includes('s')) h+=dY;
        if (handle.includes('n')) h-=dY;
        w=Math.max(40,w); h=Math.max(20,h);
        if (event.shiftKey) {
            const ratio=state.resize.startW/state.resize.startH;
            if (Math.abs(dX)>=Math.abs(dY)) h=w/ratio; else w=h*ratio;
        }
        if (state.element.tagName.toLowerCase() !== 'img') {
            const scale=w/state.resize.startW;
            const fs=Math.max(8,Math.min(160,state.resize.font*scale));
            state.element.style.fontSize=`${fs}px`;
            persist(state.element,'fontSize',`${fs}px`);
        }
        state.element.style.width=`${Math.round(w)}px`;
        state.element.style.height=state.element.tagName.toLowerCase()==='img'?`${Math.round(h)}px`:'';
        persist(state.element,'width',`${Math.round(w)}px`);
        if (state.element.tagName.toLowerCase()==='img') persist(state.element,'height',`${Math.round(h)}px`);
        positionUI();
    };

    const endHandle = () => { state.resize=null; state.rotate=null; };

    const toolbarAction = action => {
        const el=state.element; if(!el) return;
        const t=nums(el);
        const app=wire();
        if(action==='duplicate') {
            const section=el.closest(SECTION); const clone=el.cloneNode(true); clone.removeAttribute('id');
            const parent=el.parentElement; if(!parent) return;
            clone.dataset.canvaTransform=`${t.x+24},${t.y+24},${t.r}`; clone.style.transform=`translate3d(${t.x+24}px,${t.y+24}px,0) rotate(${t.r}deg)`;
            parent.insertBefore(clone,el.nextSibling);
            if(app) app.call?.('duplicateSection', sections().indexOf(section));
            notify('Elemento duplicado'); return;
        }
        if(action==='front') { el.style.zIndex=String((Number(getComputedStyle(el).zIndex)||2)+1); persist(el,'zIndex',el.style.zIndex); }
        if(action==='back') { el.style.zIndex=String(Math.max(1,(Number(getComputedStyle(el).zIndex)||2)-1)); persist(el,'zIndex',el.style.zIndex); }
        if(action==='rotate-left') writeTransform(el,t.x,t.y,t.r-90);
        if(action==='rotate-right') writeTransform(el,t.x,t.y,t.r+90);
        if(action==='center') writeTransform(el,0,0,t.r);
        if(action==='reset') { el.style.width=''; el.style.height=''; el.style.fontSize=''; writeTransform(el,0,0,0); }
        positionUI();
    };

    const notify = message => {
        window.dispatchEvent(new CustomEvent('finder-canva-notify',{detail:{message}}));
    };

    const globalKeys = event => {
        if(!isBuilder()) return;
        const typing=event.target?.isContentEditable||['input','textarea','select'].includes(String(event.target?.tagName||'').toLowerCase());
        const mod=event.ctrlKey||event.metaKey;
        if(!state.element) return;
        if(!typing && mod && event.key.toLowerCase()==='c') {
            event.preventDefault();
            state.clipboard={styles:{...state.element.style}, transform:state.element.dataset.canvaTransform||'0,0,0'};
            notify('Estilo copiado'); return;
        }
        if(!typing && mod && event.key.toLowerCase()==='v' && state.clipboard) {
            event.preventDefault();
            Object.assign(state.element.style,state.clipboard.styles);
            state.element.dataset.canvaTransform=state.clipboard.transform;
            const [x,y,r]=state.clipboard.transform.split(',').map(Number);
            writeTransform(state.element,x,y,r);
            notify('Estilo colado'); return;
        }
        if(!typing && (event.key==='Delete'||event.key==='Backspace')) {
            if(event.target.closest('[contenteditable="true"]')) return;
            event.preventDefault();
            state.element.remove();
            notify('Elemento removido do canvas');
            clear(); return;
        }
        if(!typing && event.key.startsWith('Arrow')) {
            event.preventDefault();
            const step=event.shiftKey?10:1;
            const t=nums(state.element);
            writeTransform(state.element,t.x+(event.key==='ArrowRight'?step:event.key==='ArrowLeft'?-step:0),t.y+(event.key==='ArrowDown'?step:event.key==='ArrowUp'?-step:0),t.r);
            positionUI();
        }
    };

    const install = () => {
        if(!isBuilder()) return;
        createOverlay();
        applyStored();
        if(state.listeners) return;
        state.listeners=true;

        document.addEventListener('pointerdown',event=>{
            if(!isBuilder()) return;
            if(event.target.closest('#finder-canva-ultra-overlay,#finder-canva-ultra-toolbar,#finder-builder-canva-pro-lab,#finder-builder-canva-pro-tools')) return;
            const target=elementFromPoint(event);
            if(target) { select(target); startDrag(event); return; }
            if(!event.target.closest(SECTION)) clear();
        }, true);

        document.addEventListener('pointermove',event=>{ if(state.drag) moveDrag(event); if(state.resize||state.rotate) moveHandle(event); }, true);
        document.addEventListener('pointerup',()=>{ endDrag(); endHandle(); }, true);
        document.addEventListener('pointercancel',()=>{ endDrag(); endHandle(); }, true);
        document.addEventListener('keydown',globalKeys);
        window.addEventListener('scroll',positionUI,{passive:true});
        window.addEventListener('resize',positionUI,{passive:true});
        window.addEventListener('finder-canva-select',event=>select(event.detail?.element));
    };

    const boot=()=>{ if(!isBuilder()) return; install(); if(window.Livewire) Livewire.hook('morph.updated',()=>setTimeout(()=>{install();applyStored();positionUI();},60)); };
    if(window.Livewire) boot(); else document.addEventListener('livewire:init',boot,{once:true});
})();