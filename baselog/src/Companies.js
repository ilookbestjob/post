import React from "react";
import { connect } from "react-redux";
import "./companies.css";
import Sessions from "./Sessions";



class Companies extends React.Component {
    constructor(props) {
        super(props);


 


        this.GET_SESSIONS = (pr) => {
            fetch(
                "http://localhost/nordcom/baselog/src/actions.php?action=getsessions&company=" + pr
            )
                .then(res => res.json())
                .then(
                    result => {
                        this.props.setCompany(pr);
                        this.props.setSessions(result);

                    },

                    error => {
                        console.log('er')
                    }
                );
         
        }


    }

    componentDidMount() {
        fetch(
            "http://localhost/nordcom/baselog/src/actions.php?action=getcopmanies"
        )
            .then(res => res.json())
            .then(
                result => {

                    this.props.setCompanies(result);
                
                },

                error => {
                    console.log('er')
                }
            );
    }





    render() {



        return <div className="companies__container" > {
            this.props.Data.companies.map(item => (<div><div className={item.row_id == this.props.Data.currentcompany ? "companybutton_selected" : "companybutton"} onClick={this.GET_SESSIONS.bind(this, item.row_id)}> {item.name} </div>
                <Sessions company={item.row_id} />
            </div>

            ))
        } </div>



    }
}

export default connect(
    store => ({
        Data: store
    }),
    dispatch => ({
        setCompanies: companies => {
            dispatch({ type: "SET_COMPANIES", companies: companies });

        },
        setCompany: company => {
            dispatch({ type: "SET_COMPANY", company: company });
        },

        setSessions: sessions => {
            dispatch({ type: "SET_SESSIONS", sessions: sessions });

        }
        ,

        setTags: tags => {
            dispatch({ type: "SET_TAGS", tags: tags });

        }
    }
    )
)(Companies);