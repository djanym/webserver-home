import React, { useState, useEffect } from 'react';
import axios from 'axios';
import config from './config';
import { Container, ListGroup } from 'react-bootstrap';

const Listing = () => {
    const [data, setData] = useState([]);

    useEffect(() => {
        fetchData();
    }, []);

    const fetchData = async () => {
        try {
            const response = await axios.get(
                config.api_url + '?action=get_data&type=listing'
            );
            setData(response.data);
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    };

    return (
        <Container>
            <h1>File List</h1>
            <ListGroup>
                {data.map((item, index) => (
                    <ListGroup.Item key={index}>{item}</ListGroup.Item>
                ))}
            </ListGroup>
        </Container>
    );
};

export default Listing;
