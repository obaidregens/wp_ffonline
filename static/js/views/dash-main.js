document.documentElement.appendChild(
    DOM.create('floater',{
        children: [
            DOM.create('button',{
                innerText: "<",
                listeners: {
                    click: () => sidenav.open(si)
                }
            })
        ]
    })    
);
const styling = `
sidenav > a {
    display: flex;
    width: 100%;
    padding: 12px;
    color: var(--text-color) !important;
}
sidenav > a:hover {
    background-color: var(--hover);
}
`
const si = DOM.create('sidenav',{
    children: [
        DOM.create("style",{
            innerText: styling
        }),
        DOM.create("a",{
            innerText: "Home",
            attributes: {
                href: "/dash"
            }
        }),
        DOM.create("a",{
            innerText: "Tags",
            attributes: {
                href: "/dash/tags"
            }
        }),
        DOM.create("a",{
            innerText: "Contact",
            attributes: {
                href: "/dash/contact"
            }
        }),
        DOM.create("a",{
            innerText: "FAQ",
            attributes: {
                href: "/dash/faq"
            }
        }),
        DOM.create("a",{
            innerText: "Reimport",
            attributes: {
                href: "/dash/reimport"
            }
        }),
        DOM.create("a",{
            innerText: "Beta",
            attributes: {
                href: "/dash/beta"
            }
        }),
    ],
});
sidenav.create(si);