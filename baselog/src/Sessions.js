import React from "react";
import { connect } from "react-redux";
import "./sessions.css";



class Sessions extends React.Component {
    constructor(props) {
        super(props);

        this.GET_TAGS = (session) => {
            fetch(
                "http://localhost/nordcom/baselog/src/actions.php?action=gettags&session=" + session
            )
                .then(res => res.json())
                .then(
                    result => {

                        this.props.setTags(result);

                    },

                    error => {
                        console.log('er')
                    }
                );

        }

        this.GET_LOGS = (session) => {
            this.GET_TAGS(session)
            this.props.setSession(session);
            fetch(
                "http://localhost/nordcom/baselog/src/actions.php?action=getlogs&company=" + this.props.Data.currentcompany + "&session=" + session + "&tag=" + this.props.Data.currentag
            )
                .then(res => res.json())
                .then(
                    result => {
                        this.props.setLogs(result)
                    },

                    error => {
                        console.log('er')
                    }
                );

        }

    }


    getSnapshotBeforeUpdate() {
        // this.GET_SESSIONS(this.props.Data.currentcompany);

    }

    componentWillUpdate() {
        //     this.GET_SESSIONS(this.props.Data.currentcompany);
    }

    render() {
        //this.GET_SESSIONS(this.props.Data.currentcompany);
        return (this.props.company == this.props.Data.currentcompany) ? <div className="session">{this.props.Data.sessions.map(item => (<div className={this.props.Data.currentlog == item.id ? "sessionbutton_selected" : "sessionbutton"} onClick={this.GET_LOGS.bind(this, item.id)}>{item.sessiondate}</div>))}</div> : <div></div>;


    }
}

export default connect(
    store => ({
        Data: store
    }),
    dispatch => ({
        setSession: session => {
            dispatch({ type: "SET_SESSION", session: session });

        }, setLogs: logs => {
            dispatch({ type: "SET_LOGS", logs: logs });
        }, setTags: tags => {
            dispatch({ type: "SET_TAGS", tags: tags });

        }
    })
)(Sessions);