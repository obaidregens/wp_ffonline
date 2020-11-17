DOM.q('button[label="Create"]').addEventListener('click',async () => {
    const data = Object.fromEntries(['description','duration','users','start_url','custom'].map((v,i) => {
        const selector =
        `main > text-input:nth-child(${i+1}) > input,main > text-input:nth-child(${i+1}) > textarea`;
        return [v,DOM.q(selector).value];
    }));
    const {code,beta_id} = await api('beta_session_create',{data});
    if (code > 5) {
        beta_id.forEach( error => new toast(error) );
        return;
    }
    window.location.reload();
});