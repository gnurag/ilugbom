class PeopleController < ApplicationController
  before_filter :login_required, :except => [:index, :list, :show, :login, :logout, :register, :reminder]

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def login
    if not @current_user and params[:person] and params[:person][:username] and params[:person][:password]
      user = Person.authenticate(params[:person][:username], params[:person][:password])
      if user
        set_login_cookie(user.get_psv)
        if params[:return]
          redirect_to params[:return]
        else
          redirect_to :controller => 'articles', :action => 'home'
        end
      else
        @login_failed_user = params[:person][:username]
        render :template => 'people/login'
      end
    end
  end

  def logout
    cookies.delete COOKIE_NAME if cookies[COOKIE_NAME]
    redirect_to :controller => 'articles', :action => 'home'
  end

  def register
  end

  def reminder
  end

  def list
    @person_pages, @people = paginate :people, :conditions => published_sql(self.controller_name, "visible"), :order => "people.fullname, people.created_at, people.id DESC", :per_page => 10
    @page_title = "People"
  end

  def show
    @person = Person.find(params[:id], :conditions => published_sql(self.controller_namem "visible"))
    @recent_people = Person.find(:all, :conditions => published_sql(self.controller_name, "visible"), :order => "people.fullname, people.created_at, people.id DESC", :limit => "10")
    @page_title = @person.fullname if @person
  end

  def new
    @person = Person.new
  end

  def create
    @person = Person.new(params[:person])
    if @person.save
      flash[:notice] = 'Person was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @person = Person.find(params[:id])
  end

  def update
    @person = Person.find(params[:id])
    if @person.update_attributes(params[:person])
      flash[:notice] = 'Person was successfully updated.'
      redirect_to :action => 'show', :id => @person
    else
      render :action => 'edit'
    end
  end

  def destroy
    Person.find(params[:id]).destroy
    redirect_to :action => 'list'
  end

end
