package danger_zone;
import java.util.HashMap;
import java.io.*;
import java.sql.*;
import java.sql.ResultSet;
import java.util.Vector;
import java.util.Iterator;

/**
*@author Ethan Eldridge <ejayeldridge @ gmail.com>
*@version 0.0
*@since 2012-10-22
*
* Class providing an interface to the SQL database for training purposes. 
*/
public class DataSet{
	/**
	*Connect to the database
	*/
	Connection con = null;

	/**
	*Starting size of the dataset variable. 
	*/
	static private final int dSize = 1000;

	/**
	*Vector to hold the dataset from the database
	*/
	public Vector<Tweet> dataset = new Vector<Tweet>(dSize);

	/**
	*Iterator for vector
	*/
	private Iterator<Tweet> dataIter = dataset.iterator();

	/**
	*Opens a connection to the database
	*/
	public Connection openConnection() throws Exception{
		java.util.Properties properties = new java.util.Properties();
		properties.put("user","root");
		properties.put("password","sh0wbiz1");
	  	//properties.put("characterEncoding", "ISO-8859-1");
	  	//properties.put("useUnicode", "true");
	  	String url = "jdbc:mysql://localhost/test";

	  	Class.forName("com.mysql.jdbc.Driver").newInstance();
	  	Connection c = DriverManager.getConnection(url, properties);
	  	System.out.println(c);
	  	return c;
	}

	/**
	*Returns the result set of the tweet table from the database
	*@return The result set of the tweet table
	*/
	public ResultSet getData() throws Exception{
		Statement query = con.createStatement();
		query.executeQuery("SELECT * FROM tbl_tweet");
		return query.getResultSet();
	}

	/**
	*Constructs the training set
	*@param results The ResultSet from querying the database
	*/
	public void constructTrainingDataSet(ResultSet results) throws Exception{
		try{
			//Read in the data
			while(results.next()){
				System.out.println(results.getString(3));
				int id = Integer.parseInt(results.getString(1));
				String text = results.getString(3);
				dataset.add(new Training_Tweet(id, text, NaiveBayes.convertBoolToInt(results.getBoolean(8))));
			}
		}catch(java.sql.SQLException jSQL){
			//Do nothing
		}finally{
			//Cut off the results connection
			results.close();
		}
		dataIter = dataset.iterator();
	}

	/**
	*Initalizes the data set to use the database and create an interface for the database.
	*@return Returns true if initialization succeeded.
	*/
	public boolean initialize() throws Exception{
		try{
			con = openConnection();
			System.out.println("connect");
			ResultSet data = getData();
			System.out.println("got data");
			constructTrainingDataSet(data);	
			System.out.println("constructed");
		}catch(Exception e){
			System.out.println("Exception: ");
			System.out.println(e.getStackTrace() + " " + e.getMessage());
			return false;
		}
		return true;
	}

	public Tweet getNext(){
		if(dataIter.hasNext()){
			return dataIter.next();
		}else{
			dataIter = dataset.iterator();
			return null;
		}
	}

	public static void main(String[] args) {
		DataSet d = new DataSet();
		try{
			d.initialize();
			System.out.println(d.getNext());
			System.out.println(d.getNext());
		}catch(Exception e ){
			System.out.println("Exception");
			System.out.println(e.getStackTrace());
		}

		
		System.out.println(d.dataset.size());
	}


}