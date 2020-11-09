(async () => {
    const urs = [
        "",
        "read",
        'my-stories',
        'my-stories/new',
        'global',
        '@admin',
        '@admin/stories',
        '@admin/updates',
        '@admin/collections',
        '@admin/settings',
        'inbox',
        'drafts',
        'drafts/new/edit',
        'import-stories',
        'contact',
        'connections',
        'create-fandom',
        'faq',
        'dash',
        'dash/faq',
        'dash/tags',
        'dash/contact',
        'dash/reimport',
        'manage',
        'contact',
        'rules',
        'manage',
    ];
    // remaining
    // Chapter
    // Stroy
    // story stats
    // 
    for (let i = 0; i < urs.length; i++) {
        console.log(urs[i]);
        await fetch("/" + urs[i]);
    }
})();
