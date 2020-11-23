import React from 'react';
import axios from 'axios';
import $ from 'jquery';

/**
 * Component that should be displayed on 'Order' details page of a customer.
 *
 * To be routed on '/customers/:customerId/orders' URL.
 * props:
 * - customerId: int The customer id for whom we want to see their orders
 */
export class OrderList extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            customerId: $.isNumeric(props.customerId)
                ? props.customerId
                : -1,
            customerLastName: '',
            orderList: []
        };
    }

    componentDidMount() {
        this.fetchOrdersForCustomerId(this.state.customerId);
    }

    /**
     * @returns {JSX.Element}
     */
    render() {
        return (
            <div>
                <h1>Order list for customer #{this.state.customerId}</h1>
                <div>
                    <a className="btn btn-primary" href="/customers">Back to customer list</a>
                </div>
                <div>
                    <span>Query a specific customer id</span>
                    <input className="form-control"
                        onChange={(e) => this.changeCustomerId(e)}
                        type="text"
                        placeholder="1234"
                    />
                </div>
                <div>
                    <table className="table">
                        <thead>
                            <tr>
                                <th scope="col">last_name</th>
                                <th scope="col">purchase_identifier</th>
                                <th scope="col">product_id</th>
                                <th scope="col">quantity</th>
                                <th scope="col">price</th>
                                <th scope="col">currency</th>
                                <th scope="col">date</th>
                            </tr>
                        </thead>
                        {this.renderOrderList()}
                    </table>
                </div>
                <div>
                    Total price for products bought with euros: €{this.getCurrencySpent('euros')}
                </div>
                <div>
                    Total price for products bought with dollars: ${this.getCurrencySpent('dollars')}
                </div>
            </div>
        );
    }

    /**
     * Callback function fired when user change the input value
     *
     * If the input is a number, it will try to fetch the customer with the same id.
     * The component will update itself to fetch their orders.
     *
     * @param e {event} Event object passed by <input/>
     */
    changeCustomerId(e) {
        if (!$.isNumeric(e.target.value)) {
            return;
        }

        // Input is a number (forgot to check for an integer though): we fetch the relevant customer's orders
        this.setState({
            customerId: e.target.value
        });
        this.fetchOrdersForCustomerId(e.target.value);
    }

    /**
     * Helper function that start a promise to fetch the order list for the given customer id.
     *
     * Function first check if the customer exists and then retrieve their orders if possible.
     *
     * @param customerId {int} Customer id for whom to fetch order list
     */
    fetchOrdersForCustomerId(customerId) {
        // Fetching customer first
        axios.get('/api/customers/'+customerId)
            .then((customer) => {
                // Match: we keep their last name
                this.setState({
                    customerLastName: customer.data.lastname
                });

                // Fetching their orders
                axios.get('/api/customers/'+customerId+'/orders')
                    .then((orders) => {
                        this.updateOrderList(orders.data);
                    })
                    .catch((error) => {
                        console.error(error);
                    });
            })
            .catch(() => {
                // Customer was not found: we set an error state
                this.setState({
                    customerId: -1,
                    orderList: []
                })
            });
    }

    /**
     * Iterate through the current oder list and display the sum spent in the given currency.
     *
     * @param currency {string} 'euros' or 'dollars' for now
     * @returns {number} Sum spent in the given currency
     */
    getCurrencySpent(currency) {
        let sum = 0.0;
        this.state.orderList.forEach((order) => {
           if (order.currency === currency) {
               sum += (order.price * order.quantity);
           }
        });
        return sum;
    }

    /**
     * Render the <tbody> part of the table with the data retrieved (if any) from the API.
     *
     * @returns {JSX.Element}
     */
    renderOrderList() {
        if (this.state.customerId === -1) {
            return (
                <tbody>
                    <tr>Customer does not exist</tr>
                </tbody>
            )
        } else if (this.state.orderList === []) {
            return (
                <tbody>
                    <tr>No orders found for the given customer</tr>
                </tbody>
            )
        }

        return (
            <tbody>
            {
                this.state.orderList.map(order =>
                    <tr key={order.purchase_identifier}>
                        <th scope="row">{this.state.customerLastName}</th>
                        <td>{order.purchase_identifier}</td>
                        <td>{order.product_id}</td>
                        <td>{order.quantity}</td>
                        <td>{order.price}</td>
                        <td>{order.currency}</td>
                        <td>{order.date}</td>
                    </tr>
                )
            }
            </tbody>
        );
    }

    /**
     * Update the order list with the data retrieved from the customer API
     *
     * The {object} given is the json retrieved from the endpoint '/api/customers/:customerId/orders'
     * @param data {object}
     */
    updateOrderList(data) {
        this.setState({
            orderList: data
        });
    }
}
export default OrderList;