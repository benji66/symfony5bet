
import React, {Component} from 'react';
import {withRouter} from 'react-router-dom';
import { Card, CardText, CardBody,CardTitle, Container,Row, Col, Badge  } from 'reactstrap';
import axios from 'axios';

class AsisperClase extends Component {

    constructor(props) {
        super(props);     
    }





    viewPublicResources = () => {
        this.props.history.push('/');
    }

    render() {
        return (
            <Container style={{marginTop:50}}>
                <h4 className={'text-center'}> ASISPER  </h4>
                
                <div className={'row text-center'} style={{marginTop:40}}>
                    <button
                        className={"btn btn-success text-center"}
                        onClick={() => {this.viewPublicResources()}}
                    >
                       Inicio
                    </button>
                </div>

                <div className={'row text-center'} style={{marginTop:40}}>
                    <button
                        className={"btn btn-success text-center"}
                        onClick={() => {}}
                    >
                       asistencia
                    </button>
                </div>
            </Container>
        )
    }
}
export default withRouter(AsisperClase);