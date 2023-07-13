import React from "react";
import ReactDOM from "react-dom";
import { createStore } from "redux";
import { Provider, connectAdvanced } from "react-redux";
import Companies from "./Companies";
import Logarea from "./Logarea";
import "./index.css";



//Начальное состояние приложения
const InitialState = {
 companies:[],
 currentcompany:13,
 currentlog:0,
 currenttag:0,
 sessions:[],
 errtags:[],
 logs:[]

};

//Редьюсер приложения
const Reducer = (state = InitialState, action) => {
  let newState;
  switch (action.type) {

    case "SET_COMPANIES":
      newState = {
        ...state,
        companies: action.companies,
           
      };

      return newState;
   
      case "SET_COMPANY":
        newState = {
          ...state,
          currentcompany: action.company
     
        };
  
        return newState;

   
        case "SET_SESSIONS":
          newState = {
            ...state,
            sessions: action.sessions
       
          };
    
          return newState;


          case "SET_SESSION":
            newState = {
              ...state,
              currentlog: action.session
         
            };
      
            return newState;
  

          case "SET_TAGS":
            newState = {
              ...state,
              errtags: action.tags
         
            };
      
            return newState;

            case "SET_TAG":
              newState = {
                ...state,
                currenttag: action.tag
           
              };
        
              return newState;

              

            case "SET_LOGS":
              newState = {
                ...state,
                logs: action.logs
           
              };
        
              return newState;
    default:
      return state;
  }
};


// Создание хранилища
const store = createStore(Reducer, InitialState);



//Отрисовка DOM

ReactDOM.render(
  <Provider store={store}>
    <div className="Layout">
  <div>
      <Companies/>
      </div>
      <div><Logarea/></div>
    </div>
  </Provider>,
  document.getElementById("root")
);
