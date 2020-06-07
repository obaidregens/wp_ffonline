const sortby = jQuery('.sort-collections').val(jQuery('.sort-collections').attr('value'));
function setSortParams(){
    const sortby = jQuery('.sort-collections').val();
    const sort = jQuery('.sort-order-wrapper i').hasClass('fa-sort-amount-down') ? 'DESC' : 'ASC';
    params = new URL(window.location.href).searchParams;
    params.set('sort',sort);
    params.set('sortby',sortby);
    window.location.href = '?' + params.toString();
}
jQuery('.sort-collections').change(setSortParams);
jQuery('.sort-order-wrapper button').click(function(){
    jQuery('.sort-order-wrapper i').toggleClass('fa-sort-amount-down');
    jQuery('.sort-order-wrapper i').toggleClass('fa-sort-amount-down-alt');
    setSortParams();
});