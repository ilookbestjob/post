


let quantities = [];

let states = [];
let data = [];
let toupdate = false;

const sendAction = (companyid, action) => {


    fetch("post_api.php?companyid=" + companyid + "&action=" + action);




}

const checkstates = (id) => {
    setInterval("checkstatesinterval(" + id + ")", 1000);



}

const checkstatesinterval = async (id) => {
    let res = await fetch("getstate.php?postid=" + id);
    let state = await res.text()

    let changed = false;




    let res2 = await fetch("getcurrent.php?postid=" + id);
    let state2 = await res2.text()

    $("#curenttext_" + id).html(state2)


    let res3 = await fetch("gethistory.php?postid=" + id + ($("#showblocked" + id).is(':checked') ? "&hideblocked" : ""));
    let state3 = await res3.json()


    if (!comparedata(data[id],state3)) buildhistory(state3, id)

    data[id] = state3;







    if (states[id]) {
        if (states[id] != state) {
            buildhistory(state3, id)
            changed = true;
            if (state == "0") {
                $("#buttons_" + id).html(`<div class=\"button\" onclick=\"sendAction('` + id + `', 1);\">Получить все</div>
        <div class=\"button\" onclick=\"sendAction('`+ id + `', 2);\">Получить терминалы</div>
        <div class=\"button\" onclick=\"sendAction('`+ id + `', 3);\">Получить стоимость</div>`)

                $("#statustext_" + id).html("Не работает")

            }
            else {
                $("#buttons_" + id).html(`<div class=\"button\" onclick=\"sendAction('` + id + `', 6);\">Остановить</div>`);
                $("#statustext_" + id).html(state == "1" ? "Загрузка терминалов и цен" : (state == "2" ? "Загрузка терминалов" : "Загрузка цен"))
            }
        }
    }




    states[id] = state

}

const comparedata = (data1, data2) => {
    return JSON.stringify(data2)== JSON.stringify(data1)
  }

const buildhistory = (state, id) => {
    $("#historycontainer" + id).html('<div class="historyheader"><div>Начало</div><div>Завершение</div><div>Коментарий</div><div>Статус</div><div>Тип запуска</div></div>');
    state.forEach(item => {
        $("#historycontainer" + id).append('<div class="historyrow' + (item.status == 1 ? " playing" : "") + '"><div>' + item.startdate + '</div><div>' + (item.enddate ? item.enddate : "") + '</div><div>' + (item.statistics ? item.statistics : "") + '</div><div>' + (item.status ? ["", "работает", "остановлен", "завершился", "запуск невозможен", "ошибка"][item.status] : "") + '</div><div>' + (item.starttype == "cron" ? "авто" : "ручной") + '</div></div>')
    });




}