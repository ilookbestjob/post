import React from "react";
import { connect } from "react-redux";
import "./errtags.css";



class Errtags extends React.Component {
    constructor(props) {
        super(props);

        this.GET_LOGS = (tag) => {

            this.props.setTag(tag);
            fetch(
                "http://localhost/nordcom/baselog/src/actions.php?action=getlogs&company=" + this.props.Data.currentcompany + "&session=" + + this.props.Data.currentlog + "&tag=" + tag
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


    render() {

        return <div><div className="errtag" onClick={this.GET_LOGS.bind(this, 0)}>Все</div>{this.props.Data.errtags.map(tag => (<div className="errtag" onClick={this.GET_LOGS.bind(this, tag.id)}>{tag.context}  ({tag.errcont})</div>))}</div>

    }
}

export default connect(
    store => ({
        Data: store
    }),
    dispatch => ({
        setLogs: logs => {
            dispatch({ type: "SET_LOGS", logs: logs });
        },
        setTag: tag => {
            dispatch({ type: "SET_TAG", tag: tag });
        }
    })
)(Errtags);