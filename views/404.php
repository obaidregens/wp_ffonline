<style>
    body > main {
        max-width: 500px;
        padding: 10px;
    }
    form > button {
        float: right;
    }
    form > button::before {
        font-family: 'icons';
        content: '\e927'
    }
</style>
<h1>Oops! Nothing found.</h1>
<form action="/read">
    Were you looking for a story? Perhaps searching for it might help.
    <text-input name="search" label="Search"></text-input>
    <button type="submit"></button>
</form>