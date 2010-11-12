// funkcia zisti, ci sa 'needle' nachadza v poli 'haystack'
function in_array(haystack, needle)
{
    for (key in haystack)
    {
        if (haystack[key] == needle) return true;
    }
    return false;
}

// funkcia, ktora obnovi list s miestnostami, v ktorom sa zobrazuju miestnosti pre vybranu kapacitu a typ(u cviceni)
// elm - konkretny select na vyber kapacity alebo typu.. dolezite kvoli idcku
function update_roomlist(elm) {
    var fid = $(elm).attr('id');
    var id = fid.split('_')[1];
    var capacity = $('#capacity_' + id).val();
    
    // pri cviceni ziskame typ miestnosti, pri prednaskach nie je, tak ostane prazdny
    var type = "";
    // prednasky - idcka v tvare: 1a, 1b, 2a...
    // cvicenia - idcka v tvare 1a1, 1c2, 2a1...
    if (id.length == 3) {
        var type = $('#type_' + id).val();
    }

    // pri civceniach ma pole rooms indexy v tvare capacity-type
    // pri prednaskach ma indexy v tvare capacity- (typ je vtedy "") 
    var roomlist = rooms[capacity + "-" + type];

    $('#room_list_' + id).children().remove();

    for(room in roomlist) {
        // tak si pozrieme ci nahodou nie je selected
        sel = "";
        if (in_array(rooms_selected[id], roomlist[room]["id"])) sel = 'selected="selected"';
        $('#room_list_' + id).append('<option value="' + roomlist[room]["id"] + '" '+sel+'>' + roomlist[room]["name"] + '</option>')
    }
    rooms_selected[id] = [];
}

// funkcia, ktora vygeneruje moznosti selectu na vyber kapacit
// elm - konkretny select
// capacities - kapacity, ktore maju byt v selecte na vyber
// capacity - kapacita, ktora ked sa nachadza v poli capacities, tak sa nastavi ako vybrana
function generate_capacity_options(elm, capacities, capacity) {
    elm.children().remove();
    for (cap in capacities) {
        if (capacities[cap] == capacity) elm.append('<option selected="selected">' + capacities[cap] + '</option>')
        else elm.append('<option>' + capacities[cap] + '</option>')
	}
}

// nastavi select na prazdnu hodnotu a skryje ho
function resetRoomSelect(id) {
    $('#room_select_' + id).val("0");
    $('#room_select_' + id).hide();
}
$(document).ready(function() {
    
    // ked zmenime vyber v zozname miestnosti pre danu kapacitu(typ), tak treba pomocny select na vyber miestnosti resetnut (aby to nebolo matuce)
    $('.room_list').change(function() {
        resetRoomSelect($(this).attr('id').split('_')[2]);
    });
    
    // rozlisuje sa ci je select na vyber kapacity u prednasajuceho alebo cviciaceho
    $('.capacity_teacher').change(function(){
        resetRoomSelect($(this).attr('id').split('_')[1]);
        update_roomlist(this); 
    }).change();
    
    $('.capacity_pract').change(function(){
        resetRoomSelect($(this).attr('id').split('_')[1]);
        update_roomlist(this); 
    });
    
    // cvicenia: pri zmene typu, sa vygeneruje novy select s kapacitami, ktore pre dany typ, obsahuju nejake miestnosti
    $('.type').change(function() {
        var fid = $(this).attr('id');
        var id = fid.split('_')[1];
        
        resetRoomSelect(id);
        var type = $('#type_' + id).val();	
		var capacity = $('#capacity_' + id).val(); 
        generate_capacity_options($('#capacity_' + id), types_capacities[type], capacity);
		update_roomlist(this, true);
    }).change();
    
    // selecty na vyber konkretnej miestnosti budu na zaciatku skryte
    $('.room_select').hide();
    
    // kliknutim na header 'Vybrat konkretnu miestnost' skryjeme / ukazeme konkretny select
    $('.room_select_header').click(function() {
        var fid = $(this).attr('id');
        // id ma tvar room_select_header_id
        var id = fid.split('_')[3];
        $('#room_select_' + id).toggle();
    });
    
    // vyber konkretnej miestnosti zo selectu
    // po vybere miestnosti sa nastavi spravny typ(u cviceni), spravna kapacita a v zozname miestnosti sa vyznaci vybrana miestnost
    $('.room_select').change(function() {
        var fid = $(this).attr('id');
        var id = fid.split('_')[2];
        
        // value, ktoru dostaneme ma tvar id_type_capacity
        var temp = $(this).attr('value').split('_');
        
        // ak nie je ziadna hodnota, tak sme nic nevybrali a koncime
        if (temp == "") return;
        
        var roomId = temp[0];
        var type = temp[1];	
        var capacity = temp[2];
        
        // podmienka na rozlisenie prednasok a cviceni podla dlzky idcka (u prednasok nie je select s typmi miestnosti, ale len s kapacitou)
        // prednasky - idcka v tvare: 1a, 1b, 2a...
        // cvicenia - idcka v tvare 1a1, 1c2, 2a1...
        if (id.length == 3) {
            // nastavime spravny typ a vygenerujeme novy select so spravnymi kapacitami
            $('#type_' + id).val(type);
            generate_capacity_options($('#capacity_' + id), types_capacities[type], capacity);
        }
        else {
            // pri prednaskach nastavime iba konkretnu kapacitu
            $('#capacity_' + id).val(capacity);
        }
        
        // do pola rooms_selected nastavime id miestnosti, ktoru sme si zvolili, aby bola oznacena
        rooms_selected[id] = [roomId];
        update_roomlist($('#capacity_' + id));     
    });
});
