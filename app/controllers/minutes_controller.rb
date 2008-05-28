class MinutesController < ApplicationController
  before_filter :login_required, :except => [:index, :list, :show]

  def index
    list
    render :action => 'list'
  end

  # GETs should be safe (see http://www.w3.org/2001/tag/doc/whenToUseGet.html)
  verify :method => :post, :only => [ :destroy, :create, :update ],
         :redirect_to => { :action => :list }

  def list
    @minute_pages, @minutes = paginate :minutes, :per_page => 10
  end

  def show
    @minute = Minute.find(params[:id])
  end

  def new
    @minute = Minute.new
  end

  def create
    @minute = Minute.new(params[:minute])
    if @minute.save
      flash[:notice] = 'Minute was successfully created.'
      redirect_to :action => 'list'
    else
      render :action => 'new'
    end
  end

  def edit
    @minute = Minute.find(params[:id])
  end

  def update
    @minute = Minute.find(params[:id])
    if @minute.update_attributes(params[:minute])
      flash[:notice] = 'Minute was successfully updated.'
      redirect_to :action => 'show', :id => @minute
    else
      render :action => 'edit'
    end
  end

  def destroy
    Minute.find(params[:id]).destroy
    redirect_to :action => 'list'
  end
end
