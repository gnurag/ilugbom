# Filters added to this controller apply to all controllers in the application.
# Likewise, all the methods added will be available for all controllers.

class ApplicationController < ActionController::Base
  # Pick a unique cookie name to distinguish our session data from others'
  #session :session_key => '_ilugbom_session_id'  
  include AuthSystem
  require 'openssl'
  session :disabled => true
  prepend_before_filter :get_user_from_cookie, :init_site

  def login_required
    redirect_to :controller => 'people', :action => 'login', :return => request.host_with_port+request.request_uri if not @current_user
  end

  def get_user_from_cookie
    user = get_user_from_login_cookie
    if not user.nil?
      ua = user.split("|")  # ua = user array
      @current_user = {
        :id => ua[0],
        :username => ua[1],
        :fullname => ua[2],
        :nickname => ua[3],
        :irc_nick => ua[4],
        :email => ua[5],
        :webpage => ua[6],
        :flickr_username => ua[7],
        :yahooim_username => ua[8],
        :gtalk_username => ua[9],
        :visible  => ua[10]
      }
    end
  end

  def init_site
    @nav_pages = Page.find(:all, :conditions => "pages.published = 1", :order => "pages.order_by,pages.id ASC")
  end

  private
  def published_sql(tablename="", fieldname = "published", extra_sql = "")
    conditions = @current_user ? " 1=1 " : "#{tablename}.#{fieldname} = 1"
    conditions += extra_sql
  end
  
end
