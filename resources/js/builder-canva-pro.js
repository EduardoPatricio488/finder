(function () {
    'use strict';

    const rootSelector = '[wire\\:id]';
    const sectionSelector = '[wire\\:key^="builder-section-"]';
    const state = {
        mounted: false,
        guides: false,
        safeArea: false,
        multi: new Set(),
        clipboard: null,
        panel: null,
        selectedPanel: null,
    };

    const isBuilder = () => Boolean(document.querySelector(rootSelector)) && /\/websites\/[^/]+\/builder$/.test(window.location.pathname);
    const root = () => document.querySelector(rootSelector);
    const wire = () => {
        const node = root();
        return node && window.Livewire ? window.Livewire.find(node.getAttribute('wire:id')) : null;
    };
    const sections = () => Array.from(document.querySelectorAll(sectionSelector));
    const escape = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    const notify = message => window.dispatchEvent(new CustomEvent('finder-canva-notify', { detail: { message } }));

    const theme = () => {
        const app = wire();
        try { return app?.get?.('theme') || {}; } catch { return {}; }
    };

    const setLivewire = (path, value) => {
        const app = wire();
        if (!app) return false;
        try { app.set(path, value); return true; } catch { return false; }
    };

    const selected = () => document.querySelector('.finder-canva-selected-element');

    const metaFor = element => {
        const section = element?.closest(sectionSelector);
        if (!section) return null;
        const index = sections().indexOf(section);
        const type = section.dataset.builderSectionType || '';
        const headings = Array.from(section.querySelectorAll('h1,h2,h3')).filter(node => !node.closest('button,a'));
        const paragraphs = Array.from(section.querySelectorAll('p')).filter(node => !node.closest('button,a'));
        const articles = Array.from(section.querySelectorAll('article'));
        if (headings[0] === element) return { index, slot: 'title', kind: 'text' };
        if (type === 'hero' && paragraphs[0] === element) return { index, slot: 'subtitle', kind: 'text' };
        if (type === 'hero' && paragraphs[1] === element) return { index, slot: 'description', kind: 'text' };
        if (paragraphs[0] === element) return { index, slot: type === 'text' ? 'body' : 'description', kind: 'text' };
        const article = element.closest('article');
        if (article) {
            const itemIndex = articles.indexOf(article);
            const itemHeading = article.querySelector('p.font-black,h3,h4');
            const itemParagraph = article.querySelector('p.mt-2,p:not(.font-black)');
            if (itemHeading === element) {
                const field = type === 'faq' ? 'question' : type === 'testimonials' ? 'name' : 'title';
                return { index, slot: `item.${itemIndex}.${field}`, kind: 'text', itemIndex };
            }
            if (itemParagraph === element) {
                const field = type === 'faq' ? 'answer' : type === 'testimonials' ? 'quote' : type === 'pricing' ? 'price' : 'description';
                return { index, slot: `item.${itemIndex}.${field}`, kind: 'text', itemIndex };
            }
        }
        if (element.matches('img')) return { index, slot: 'image', kind: 'image' };
        return null;
    };

    const styleFor = (element, key, fallback = '') => {
        const meta = metaFor(element);
        if (!meta) return fallback;
        const app = wire();
        try {
            return app?.get?.(`sections.${meta.index}.settings.elements.${meta.slot}.${key}`) ?? fallback;
        } catch { return fallback; }
    };

    const persist = (element, key, value) => {
        const meta = metaFor(element);
        if (!meta) return;
        setLivewire(`sections.${meta.index}.settings.elements.${meta.slot}.${key}`, value);
    };

    const sectionSettings = index => {
        const app = wire();
        try { return app?.get?.(`sections.${index}.settings`) || {}; } catch { return {}; }
    };

    const applySections = () => {
        sections().forEach((section, index) => {
            const settings = sectionSettings(index);
            section.dataset.builderSectionIndex = String(index);
            const inner = section.querySelector(':scope > div:last-child') || section.querySelector(':scope > div');
            if (inner) {
                const width = settings.content_width || settings.width;
                if (width) inner.style.maxWidth = width;
                if (settings.align) inner.style.textAlign = settings.align;
            }
            if (settings.min_height) section.style.minHeight = settings.min_height;
            if (settings.background) section.style.background = settings.background === 'transparent' ? '' : settings.background;
            if (settings.section_gap) section.style.gap = settings.section_gap;
        });
    };

    const addOption = (parent, label, value, current, callback) => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `fcp-option${String(current) === String(value) ? ' is-active' : ''}`;
        button.textContent = label;
        button.addEventListener('click', () => {
            callback(value);
            parent.querySelectorAll('.fcp-option').forEach(node => node.classList.remove('is-active'));
            button.classList.add('is-active');
        });
        parent.appendChild(button);
    };

    const row = (label, control) => {
        const wrapper = document.createElement('div');
        wrapper.className = 'mb-3';
        const title = document.createElement('label');
        title.textContent = label;
        wrapper.append(title, control);
        return wrapper;
    };

    const openDesignLab = () => {
        let panel = document.getElementById('finder-builder-canva-pro-lab');
        if (!panel) {
            panel = document.createElement('div');
            panel.id = 'finder-builder-canva-pro-lab';
            panel.className = 'finder-canva-pro-panel fixed right-4 top-20 z-[275] overflow-y-auto';
            document.body.appendChild(panel);
        }
        state.panel = panel;
        const currentTheme = theme();
        panel.innerHTML = `
            <div class="sticky top-0 z-10 flex items-center justify-between border-b border-zinc-100 bg-white/95 px-4 py-4 backdrop-blur-xl">
                <div><div class="text-[9px] font-black uppercase tracking-[.16em] text-zinc-400">Design Studio</div><div class="mt-1 text-sm font-black">Sistema visual</div></div>
                <button type="button" data-close class="rounded-lg px-2 py-1 text-xl text-zinc-400 hover:bg-zinc-100">×</button>
            </div>
            <div class="fcp-section" data-theme></div>
            <div class="fcp-section" data-presets></div>
            <div class="fcp-section" data-section-style></div>
            <div class="fcp-section" data-shortcuts></div>
        `;

        panel.querySelector('[data-close]').addEventListener('click', () => panel.classList.add('hidden'));
        const themeHost = panel.querySelector('[data-theme]');
        themeHost.innerHTML = '<div class="mb-3 text-xs font-black">Tema global</div>';

        const colors = [
            ['Primária', 'primary', currentTheme.primary || '#635bff'],
            ['Secundária', 'secondary', currentTheme.secondary || '#111827'],
            ['Fundo', 'background', currentTheme.background || '#ffffff'],
            ['Texto', 'text', currentTheme.text || '#111827'],
        ];
        colors.forEach(([label, key, value]) => {
            const input = document.createElement('input');
            input.type = 'color'; input.value = /^#[0-9a-f]{6}$/i.test(value) ? value : '#ffffff';
            input.addEventListener('input', () => setLivewire(`theme.${key}`, input.value));
            const line = document.createElement('div');
            line.className = 'mb-2 flex items-center justify-between rounded-xl border border-zinc-100 px-3 py-2';
            line.innerHTML = `<span class="text-[10px] font-black text-zinc-600">${escape(label)}</span>`;
            line.appendChild(input); themeHost.appendChild(line);
        });

        const fonts = document.createElement('select');
        ['Inter','Instrument Sans','DM Sans','Manrope','Plus Jakarta Sans','Space Grotesk','Playfair Display','Source Serif 4'].forEach(font => fonts.add(new Option(font, font)));
        fonts.value = currentTheme.heading_font || 'Inter';
        fonts.addEventListener('change', () => { setLivewire('theme.heading_font', fonts.value); notify(`Fonte dos títulos: ${fonts.value}`); });
        themeHost.appendChild(row('Fonte dos títulos', fonts));

        const bodyFonts = fonts.cloneNode(true);
        bodyFonts.value = currentTheme.body_font || 'Inter';
        bodyFonts.addEventListener('change', () => setLivewire('theme.body_font', bodyFonts.value));
        themeHost.appendChild(row('Fonte do texto', bodyFonts));

        const presets = panel.querySelector('[data-presets]');
        presets.innerHTML = '<div class="mb-3 text-xs font-black">Estilos rápidos</div><div class="fcp-grid" data-preset-grid></div>';
        const presetGrid = presets.querySelector('[data-preset-grid]');
        const presetData = {
            Minimal: { primary:'#111827',secondary:'#52525b',background:'#ffffff',text:'#18181b',radius:'none',shadow:'none',heading_font:'Inter',body_font:'Inter' },
            Modern: { primary:'#635bff',secondary:'#111827',background:'#ffffff',text:'#111827',radius:'lg',shadow:'sm',heading_font:'Inter',body_font:'Inter' },
            Editorial: { primary:'#111111',secondary:'#7c2d12',background:'#faf7f2',text:'#18181b',radius:'sm',shadow:'none',heading_font:'Playfair Display',body_font:'Inter' },
            Bold: { primary:'#ec4899',secondary:'#111827',background:'#0f0f12',text:'#ffffff',radius:'lg',shadow:'md',heading_font:'Space Grotesk',body_font:'Inter' },
            Soft: { primary:'#7c3aed',secondary:'#4c1d95',background:'#fafafa',text:'#27272a',radius:'lg',shadow:'md',heading_font:'Manrope',body_font:'Manrope' },
            Mono: { primary:'#16a34a',secondary:'#052e16',background:'#fafafa',text:'#18181b',radius:'sm',shadow:'none',heading_font:'Space Grotesk',body_font:'Space Grotesk' },
        };
        Object.entries(presetData).forEach(([name, values]) => {
            addOption(presetGrid, name, name, '', () => {
                Object.entries(values).forEach(([key, value]) => setLivewire(`theme.${key}`, value));
                applySections();
                notify(`Estilo ${name} aplicado`);
            });
        });

        const sectionHost = panel.querySelector('[data-section-style]');
        const section = selected()?.closest(sectionSelector);
        const index = section ? sections().indexOf(section) : -1;
        const settings = index >= 0 ? sectionSettings(index) : {};
        sectionHost.innerHTML = '<div class="mb-3 text-xs font-black">Secção seleccionada</div>';
        if (index < 0) {
            sectionHost.insertAdjacentHTML('beforeend', '<div class="rounded-xl bg-zinc-50 p-3 text-[10px] font-bold text-zinc-500">Selecciona um elemento para editar a sua secção.</div>');
        } else {
            const grid = document.createElement('div'); grid.className = 'fcp-grid';
            [['Sem fundo','transparent'],['Claro','#ffffff'],['Suave','#f4f4f5'],['Escuro','#18181b'],['Primário',currentTheme.primary || '#635bff'],['Custom','#ece7ff']].forEach(([label,value]) => addOption(grid,label,value,settings.background || 'transparent',v=>{ setLivewire(`sections.${index}.settings.background`,v); section.style.background=v==='transparent'?'':v; }));
            sectionHost.appendChild(grid);
            const pad = document.createElement('div'); pad.className='fcp-grid mt-3';
            [['Compacta','sm'],['Normal','md'],['Grande','lg'],['Máxima','xl']].forEach(([label,value]) => addOption(pad,label,value,settings.padding || 'lg',v=>{ setLivewire(`sections.${index}.settings.padding`,v); const map={sm:'3rem',md:'4.5rem',lg:'6rem',xl:'8rem'}; section.style.paddingTop=map[v]; section.style.paddingBottom=map[v]; }));
            sectionHost.appendChild(pad);
            const width = document.createElement('input'); width.type='text'; width.value=settings.content_width || '1200px'; width.placeholder='ex.: 1200px / 90%'; width.addEventListener('change',()=>{setLivewire(`sections.${index}.settings.content_width`,width.value); applySections();});
            sectionHost.appendChild(row('Largura do conteúdo',width));
            const height = document.createElement('select'); [['Auto',''],['Pequena','420px'],['Ecrã','calc(100vh - 90px)'],['Grande','900px']].forEach(([label,value])=>height.add(new Option(label,value))); height.value=settings.min_height || ''; height.addEventListener('change',()=>{setLivewire(`sections.${index}.settings.min_height`,height.value); section.style.minHeight=height.value;});
            sectionHost.appendChild(row('Altura mínima',height));
        }

        panel.querySelector('[data-shortcuts]').innerHTML = '<div class="mb-2 text-xs font-black">Atalhos</div><div class="grid grid-cols-2 gap-2 text-[10px] font-bold text-zinc-500"><div class="rounded-xl bg-zinc-50 p-2">Shift + clique<br><b class="text-zinc-900">Multi-selecção</b></div><div class="rounded-xl bg-zinc-50 p-2">Ctrl/Cmd + C<br><b class="text-zinc-900">Copiar estilo</b></div><div class="rounded-xl bg-zinc-50 p-2">Ctrl/Cmd + V<br><b class="text-zinc-900">Colar estilo</b></div><div class="rounded-xl bg-zinc-50 p-2">Ctrl/Cmd + Shift + G<br><b class="text-zinc-900">Guias</b></div></div>';
        panel.classList.remove('hidden');
    };

    const makeFloatingTools = () => {
        if (document.getElementById('finder-builder-canva-pro-tools')) return;
        const bar = document.createElement('div');
        bar.id='finder-builder-canva-pro-tools';
        bar.className='finder-canva-pro-floating-tools';
        bar.innerHTML='<button data-action="design">✦ Design</button><button data-action="guides">▦ Guias</button><button data-action="safe">⌑ Área segura</button><button data-action="fit">⌗ Enquadrar</button><button data-action="focus">◉ Foco</button>';
        bar.querySelector('[data-action="design"]').addEventListener('click',openDesignLab);
        bar.querySelector('[data-action="guides"]').addEventListener('click',()=>{state.guides=!state.guides;document.body.classList.toggle('finder-canva-pro-guides-on',state.guides);});
        bar.querySelector('[data-action="safe"]').addEventListener('click',()=>{state.safeArea=!state.safeArea;document.body.classList.toggle('finder-canva-pro-safe-area',state.safeArea);});
        bar.querySelector('[data-action="fit"]').addEventListener('click',()=>{document.querySelector('[data-finder-site-canvas]')?.scrollIntoView({behavior:'smooth',block:'center'});});
        bar.querySelector('[data-action="focus"]').addEventListener('click',()=>{const element=selected(); element?.scrollIntoView({behavior:'smooth',block:'center'});});
        document.body.appendChild(bar);
    };

    const addRulers = () => {
        if (!document.getElementById('finder-builder-canva-pro-ruler-x')) {
            const x=document.createElement('div'); x.id='finder-builder-canva-pro-ruler-x'; x.className='finder-canva-pro-ruler-x'; document.body.appendChild(x);
            const y=document.createElement('div'); y.id='finder-builder-canva-pro-ruler-y'; y.className='finder-canva-pro-ruler-y'; document.body.appendChild(y);
        }
    };

    const multiSelect = event => {
        if (!isBuilder()) return;
        const target = event.target.closest('.finder-canva-editable,.finder-canva-image,[contenteditable="true"],h1,h2,h3,p,article');
        if (!target || target.closest('#finder-builder-canva-pro-lab,#finder-builder-canva-pro-tools,#finder-builder-canva-dock,#finder-builder-canva-toolbar,#finder-builder-canva-inspector,#finder-builder-canva-palette')) return;
        if (!event.shiftKey) {
            state.multi.clear();
            document.querySelectorAll('.finder-canva-pro-multi').forEach(node=>node.classList.remove('finder-canva-pro-multi'));
            return;
        }
        event.preventDefault();
        if (state.multi.has(target)) {
            state.multi.delete(target); target.classList.remove('finder-canva-pro-multi');
        } else {
            state.multi.add(target); target.classList.add('finder-canva-pro-multi');
        }
    };

    const applyStyleClipboard = (from, to) => {
        if (!from || !to) return;
        const keys=['x','y','width','fontSize','color','opacity','textAlign','zIndex'];
        keys.forEach(key=>{const value=styleFor(from,key,''); if(value!==''){ if(key==='x'||key==='y'||key==='zIndex') to.style[key==='zIndex'?'zIndex':'transform']=key==='transform' ? value : to.style[key]; persist(to,key,value);} });
    };

    const keyboard = event => {
        if (!isBuilder()) return;
        const typing = event.target?.isContentEditable || ['input','textarea','select'].includes(String(event.target?.tagName||'').toLowerCase());
        if (typing) return;
        const modifier=event.ctrlKey||event.metaKey;
        const active=selected();
        if (modifier && event.shiftKey && event.key.toLowerCase()==='g') { event.preventDefault(); state.guides=!state.guides; document.body.classList.toggle('finder-canva-pro-guides-on',state.guides); return; }
        if (modifier && event.key.toLowerCase()==='c' && active) { event.preventDefault(); state.clipboard={...['fontSize','color','opacity','textAlign','width'].reduce((obj,key)=>{obj[key]=styleFor(active,key,'');return obj;},{}), x:styleFor(active,'x',0), y:styleFor(active,'y',0), zIndex:styleFor(active,'zIndex','')}; notify('Estilo copiado'); return; }
        if (modifier && event.key.toLowerCase()==='v' && active && state.clipboard) { event.preventDefault(); Object.entries(state.clipboard).forEach(([key,value])=>{if(value!==''&&value!==undefined){if(key==='fontSize')active.style.fontSize=value;if(key==='color')active.style.color=value;if(key==='opacity')active.style.opacity=value;if(key==='textAlign')active.style.textAlign=value;if(key==='width')active.style.width=value;persist(active,key,value);}});notify('Estilo colado');return; }
        const targets=state.multi.size ? Array.from(state.multi) : active ? [active] : [];
        const step=event.shiftKey?10:1;
        if (targets.length && ['ArrowLeft','ArrowRight','ArrowUp','ArrowDown'].includes(event.key)) {
            event.preventDefault();
            targets.forEach(node=>{
                const transform=getComputedStyle(node).transform;
                let x=0,y=0; const match=transform.match(/matrix(3d)?\\(([^)]+)\\)/); if(match){const m=match[2].split(',').map(Number);x=match[1]==='3d'?(m[12]||0):(m[4]||0);y=match[1]==='3d'?(m[13]||0):(m[5]||0);} if(event.key==='ArrowLeft')x-=step;if(event.key==='ArrowRight')x+=step;if(event.key==='ArrowUp')y-=step;if(event.key==='ArrowDown')y+=step;node.style.position='relative';node.style.transform=`translate3d(${x}px,${y}px,0)`;persist(node,'x',Math.round(x));persist(node,'y',Math.round(y));
            });
            window.dispatchEvent(new Event('resize'));
        }
        if (event.key==='Escape') { state.multi.clear(); document.querySelectorAll('.finder-canva-pro-multi').forEach(node=>node.classList.remove('finder-canva-pro-multi')); state.panel?.classList.add('hidden'); }
    };

    const install = () => {
        if (!isBuilder()) return;
        const builderRoot=document.querySelector(rootSelector);
        builderRoot?.classList.add('finder-canva-pro-root');
        addRulers();
        makeFloatingTools();
        applySections();
        if (!state.mounted) notify('Canva Pro activado: Shift + clique para seleccionar vários elementos.');
        state.mounted=true;
    };

    document.addEventListener('click',multiSelect,true);
    document.addEventListener('keydown',keyboard,true);
    window.addEventListener('scroll',()=>{ if(state.panel && !state.panel.classList.contains('hidden')) return; });

    const boot=()=>{if(!isBuilder())return;install();window.Livewire?.hook('morph.updated',()=>setTimeout(install,50));};
    if(window.Livewire)boot();else document.addEventListener('livewire:init',boot,{once:true});
})();
