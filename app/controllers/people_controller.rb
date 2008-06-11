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
    @person_pages, @people = paginate :people, :conditions => published_sql(self.controller_name, "visible", " AND people.deleted = 0"), :order => "people.fullname, people.created_at, people.id DESC", :per_page => 10
    @page_title = "People"
  end

  def show
    @person = Person.find(params[:id], :conditions => published_sql(self.controller_name, "visible", " AND people.deleted=0 "))
    @recent_articles = Article.find(:all, :conditions => published_sql("articles", "published"), :order => "articles.created_at DESC", :limit => 10)
    @recent_minutes  = Minute.find(:all, :conditions => published_sql("minutes", "published"), :include => [:event], :order => "minutes.created_at DESC", :limit => 10)
    @recent_people = Person.find(:all, :conditions => published_sql(self.controller_name, "visible", " AND people.deleted = 0"), :order => "people.created_at, people.fullname, people.id DESC", :limit => "10")
    @page_title = @person.fullname if @person
  end

  def new
    @person = Person.new
  end

  def create
    @person = Person.new(params[:person])
    @person.password = Person.hash_user_password(params[:person][:password])
    #params[:person][:password_confirmation] = Person.hash_user_password(params[:person][:password_confirmation])
    p params[:person]
    if @person.save
      flash[:notice] = 'Person was successfully created.'
      redirect_to :action => 'list'
    else
      @person.password = params[:person][:password]
      render :action => 'new'
    end
  end

  def edit
    @person = Person.find(params[:id])
    @person.password = ""
  end

  def update
    @person = Person.find(params[:id])
    @person.hash_user_password(params[:person][:password])
    if @person.update_attributes(params[:person])
      flash[:notice] = 'Person was successfully updated.'
      redirect_to :action => 'show', :id => @person
    else
      @person.password = params[:person][:password]
      render :action => 'edit'
    end
  end

  def destroy
    Person.find(params[:id]).deleted = 1
    redirect_to :action => 'list'
  end

end
