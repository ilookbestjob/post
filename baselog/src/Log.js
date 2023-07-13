import React from "react";
import { connect } from "react-redux";
import "./log.css";




class Log extends React.Component {
    constructor(props) {
        super(props);




    }
    render() {

        const ERR_TYPE = ["Инфо", "Предупреждение", "Ошибка", "Критическая ошибка"];


        return <div className={this.props.Data.logs.length==0?"hidden":"logg"}>
            {this.props.Data.logs.map(log => (<div className="log__container">
                <div>{ERR_TYPE[log.logtype]}</div><div>{this.props.Data.errtags.find(item => item.id == log.logcontext)?this.props.Data.errtags.find(item => item.id == log.logcontext).context:""}</div><div>{log.logtext}</div>
            </div>))}





        </div>

    }
}

export default connect(
    store => ({
        Data: store
    }),
    dispatch => ({
        setWidth: Areawidth => {
            dispatch({ type: "SET_WIDTH", width: Areawidth });
        }
    })
)(Log);