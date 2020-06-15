var start = document.createElement("input");
start.setAttribute("type", "hidden");
start.setAttribute("id", "thesaurus_start");
document.body.appendChild(start);

var end = document.createElement("input");
end.setAttribute("type", "hidden");
end.setAttribute("id", "thesaurus_end");
document.body.appendChild(end);

var pagex = document.createElement("input");
pagex.setAttribute("type", "hidden");
pagex.setAttribute("id", "thesaurus_pagex");
document.body.appendChild(pagex);

var pagey = document.createElement("input");
pagey.setAttribute("type", "hidden");
pagey.setAttribute("id", "thesaurus_pagey");
document.body.appendChild(pagey);

var search_for = document.createElement("input");
search_for.setAttribute("type", "hidden");
search_for.setAttribute("id", "thesaurus_search_for");
document.body.appendChild(search_for);

var menu = document.createElement('menu');
menu.setAttribute("class","menu");
menu.setAttribute("id","thesaurus_menu");
document.body.appendChild(menu);