import React from 'react';
import axios from 'axios';

/**
 * Component that should be displayed on 'Customer' list page.
 *
 * To be routed on '/customers/' URL
 */
export class CustomerList extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            customerList: []
        };
    }

    componentDidMount() {
        this.fetchCustomerList()
    }

    render() {
        return (
            <div>
                <h1>Customer list</h1>
                <div>
                    <table className="table">
                        <thead>
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">title</th>
                                <th scope="col">lastname</th>
                                <th scope="col">firstname</th>
                                <th scope="col">postal_code</th>
                                <th scope="col">city</th>
                                <th scope="col">email</th>
                                <th scope="col">orders</th>
                            </tr>
                        </thead>
                        {this.renderCustomerList()}
                    </table>
                </div>
            </div>
        );
    }

    /**
     * Create a promise to fetch the customer list from endpoint '/api/customers'
     */
    fetchCustomerList() {
        axios.get('/api/customers')
            .then((response) => {
                this.updateCustomerList(response.data)
            })
            .catch((error) => {
                console.error(error);
            });
    }

    /**
     * @returns {JSX.Element}
     */
    renderCustomerList() {
        if (this.state.customerList === []) {
            return (
                <tbody>
                    <tr>Nothing customer found</tr>
                </tbody>
            );
        }

        return (
            <tbody>
            {
                this.state.customerList.map(customer =>
                    <tr key={customer.id}>
                        <th scope="row">{customer.id}</th>
                        <td>{customer.title}</td>
                        <td>{customer.lastname}</td>
                        <td>{customer.firstname}</td>
                        <td>{customer.postal_code}</td>
                        <td>{customer.city}</td>
                        <td>{customer.email}</td>
                        <td><a href={'/customers/'+customer.id+'/orders'}>show orders</a></td>
                    </tr>
                )
            }
            </tbody>
        );
    }

    /**
     * Update the customer list with the data retrieved from the customer API
     *
     * The {object} given is the json retrieved from the endpoint '/api/customers'
     * @param data {object}
     */
    updateCustomerList(data) {
        this.setState({
            customerList: data
        })
    }
}
export default CustomerList;