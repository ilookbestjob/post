import React from "react";
import { connect } from "react-redux";
import "./logarea.css";
import Sessions from "./Sessions";
import Errtags from "./Errtags"
import Log from "./Log"



class Logarea extends React.Component {
    constructor(props) {
        super(props);




    }
    render() {
    
    
    return <div className="logarea">

<div className="logarea__tags"><Errtags/></div>

<div><Log/></div>


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
)(Logarea);