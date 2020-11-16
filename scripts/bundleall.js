const ref = [
    "author-collections",
    "author-about",
    "author-updates","author-stories","author-settings","global","inbox","drafts-index","drafts-editSlate-ps","drafts-edit","react-select-ps","edit-book","import-stories","contact","connections-home","create-fandom","faq","dash-home","dash-faq","dash-tags","dash-contact","dash-reimport","manage","chapters","react-ps","book","story-stats","collection-single","drafts-editSlateDev","drafts-editSlateDev1","drafts-preview"];
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
        'rules',
    ];
    // remaining
    // Chapter
    // Stroy
    // story stats
    // Drafts Preview
    // Manage
    for (let i = 0; i < urs.length; i++) {
        console.log(urs[i]);
        await fetch("/" + urs[i]);
    }
})();
